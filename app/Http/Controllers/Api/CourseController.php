<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CourseController extends Controller
{
    private function authUser(Request $request): User
    {
        $user = $request->attributes->get('auth_user');
        if (!$user instanceof User) {
            throw new HttpException(401, 'Unauthenticated');
        }
        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->authUser($request);
        $q = trim((string) $request->query('q', ''));

        $courses = Course::query()
            ->where('user_id', $user->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->get();

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $user = $this->authUser($request);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->where(fn ($q) => $q->where('user_id', $user->id))],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'code' => trim($data['code']),
            'name' => trim($data['name']),
        ]);

        return response()->json(['id' => $course->id]);
    }

    public function update(Request $request, int $id)
    {
        $user = $this->authUser($request);
        $course = Course::query()->where('user_id', $user->id)->findOrFail($id);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('courses', 'code')
                ->where(fn ($q) => $q->where('user_id', $user->id))
                ->ignore($course->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $course->update([
            'code' => trim($data['code']),
            'name' => trim($data['name']),
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $this->authUser($request);
        $course = Course::query()->where('user_id', $user->id)->findOrFail($id);
        $course->delete();

        return response()->json(['ok' => true]);
    }
}
