<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    private const CAPTCHA_TTL_SECONDS = 300;
    private const CAPTCHA_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private function randomCaptchaText(int $length = 5): string
    {
        $maxIndex = strlen(self::CAPTCHA_CHARS) - 1;
        $text = '';

        for ($i = 0; $i < $length; $i++) {
            $text .= self::CAPTCHA_CHARS[random_int(0, $maxIndex)];
        }

        return $text;
    }

    private function buildCaptchaSvgDataUri(string $text): string
    {
        $width = 170;
        $height = 56;
        $chars = str_split($text);
        $charCount = count($chars);
        $charStep = ($width - 30) / max($charCount, 1);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        $svg .= '<defs>';
        $svg .= '<linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">';
        $svg .= '<stop offset="0%" stop-color="#f8fafc" /><stop offset="100%" stop-color="#e2e8f0" />';
        $svg .= '</linearGradient>';
        $svg .= '</defs>';
        $svg .= '<rect width="100%" height="100%" rx="8" fill="url(#bg)" />';

        for ($i = 0; $i < 6; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $stroke = random_int(130, 190);
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="rgb(' . $stroke . ',' . ($stroke - 20) . ',' . ($stroke + 10) . ')" stroke-width="1.2" opacity="0.5" />';
        }

        for ($i = 0; $i < 30; $i++) {
            $cx = random_int(4, $width - 4);
            $cy = random_int(4, $height - 4);
            $r = random_int(1, 2);
            $shade = random_int(135, 185);
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="rgb(' . $shade . ',' . $shade . ',' . ($shade + 15) . ')" opacity="0.45" />';
        }

        foreach ($chars as $index => $char) {
            $x = 16 + ($index * $charStep) + random_int(-2, 2);
            $y = random_int(35, 43);
            $rotate = random_int(-18, 18);
            $dark = random_int(20, 60);
            $svg .= '<text x="' . $x . '" y="' . $y . '" fill="rgb(' . $dark . ',' . $dark . ',' . ($dark + 12) . ')" font-size="30" font-family="monospace" font-weight="700" transform="rotate(' . $rotate . ' ' . $x . ' ' . $y . ')">' . $char . '</text>';
        }

        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function authUser(Request $request): User
    {
        $user = $request->attributes->get('auth_user');
        if (!$user instanceof User) {
            throw new HttpException(401, 'Unauthenticated');
        }
        return $user;
    }

    private function mustEmailRule(): string
    {
        return 'regex:/^[^@\s]+@([^.@\s]+\.)*must\.edu\.my$/i';
    }

    private function emailValidationMessages(): array
    {
        return [
            'email.regex' => 'Please use a MUST email only (@must.edu.my or subdomain @*.must.edu.my).',
        ];
    }

    private function validateCaptcha(string $captchaId, string $captchaAnswer): void
    {
        $key = "auth_captcha:{$captchaId}";
        $expected = Cache::get($key);
        Cache::forget($key);

        if (!$expected || strtoupper(trim($captchaAnswer)) !== strtoupper((string) $expected)) {
            throw ValidationException::withMessages([
                'captcha' => ['Invalid captcha.'],
            ]);
        }
    }

    public function captcha()
    {
        $captchaId = Str::uuid()->toString();
        $captchaText = $this->randomCaptchaText();
        $captchaImage = $this->buildCaptchaSvgDataUri($captchaText);

        Cache::put("auth_captcha:{$captchaId}", $captchaText, now()->addSeconds(self::CAPTCHA_TTL_SECONDS));

        return response()->json([
            'captcha_id' => $captchaId,
            'captcha_image' => $captchaImage,
            'expires_in' => self::CAPTCHA_TTL_SECONDS,
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate(
            [
                'student_name' => ['required', 'string', 'max:255'],
                'student_id' => ['required', 'string', 'max:50'],
                'phone' => ['required', 'string', 'max:30'],
                'email' => ['required', 'email:rfc', $this->mustEmailRule(), 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'captcha_id' => ['required', 'string'],
                'captcha_answer' => ['required', 'string'],
            ],
            $this->emailValidationMessages()
        );

        $this->validateCaptcha($data['captcha_id'], $data['captcha_answer']);

        $user = User::create([
            'name' => trim($data['student_name']),
            'student_id' => trim($data['student_id']),
            'phone' => trim($data['phone']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'api_token' => Str::random(80),
        ]);

        return response()->json([
            'token' => $user->api_token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'email' => $user->email,
            ],
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(
            [
                'email' => ['required', 'email:rfc', $this->mustEmailRule()],
                'password' => ['required', 'string'],
                'captcha_id' => ['required', 'string'],
                'captcha_answer' => ['required', 'string'],
            ],
            $this->emailValidationMessages()
        );

        $this->validateCaptcha($data['captcha_id'], $data['captcha_answer']);

        $user = User::query()->where('email', strtolower(trim($data['email'])))->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $user->update(['api_token' => Str::random(80)]);

        return response()->json([
            'token' => $user->api_token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'email' => $user->email,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->authUser($request);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'student_id' => $user->student_id,
            'phone' => $user->phone,
            'email' => $user->email,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $this->authUser($request);
        $user->update(['api_token' => null]);

        return response()->json(['ok' => true]);
    }

    public function changePassword(Request $request)
    {
        $user = $this->authUser($request);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => $data['password'],
            'api_token' => Str::random(80),
        ]);

        return response()->json(['ok' => true]);
    }
}
