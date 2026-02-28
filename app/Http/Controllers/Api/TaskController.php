<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskController extends Controller
{
    private const CATEGORIES = [
        'ASSIGNMENT',
        'ACTIVITY',
        'TUTORIAL',
        'MIDTERM',
        'EXAM',
        'PRESENTATION',
        'PROJECT',
        'OTHERS',
    ];

    private const STATUSES = [
        'PENDING',
        'DONE',
    ];

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
        $courseId = $request->query('course_id');
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $tasks = Task::query()
            ->whereHas('course', fn ($q) => $q->where('user_id', $user->id))
            ->when($courseId !== null, fn ($query) => $query->where('course_id', $courseId))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->orderBy('due_at')
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $user = $this->authUser($request);
        $data = $request->validate([
            'course_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:'.implode(',', self::CATEGORIES)],
            'due_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $course = Course::query()->where('user_id', $user->id)->find($data['course_id']);
        if (!$course) {
            return response()->json(['message' => 'course_id not found'], 422);
        }

        $task = Task::create([
            'course_id' => $data['course_id'],
            'title' => trim($data['title']),
            'category' => $data['category'],
            'due_at' => $data['due_at'],
            'notes' => $data['notes'] ?? null,
            'status' => 'PENDING',
        ]);

        return response()->json(['id' => $task->id]);
    }

    public function update(Request $request, int $id)
    {
        $user = $this->authUser($request);
        $task = Task::query()
            ->whereHas('course', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $data = $request->validate([
            'course_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:'.implode(',', self::CATEGORIES)],
            'due_at' => ['required', 'date'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        $course = Course::query()->where('user_id', $user->id)->find($data['course_id']);
        if (!$course) {
            return response()->json(['message' => 'course_id not found'], 422);
        }

        $task->update([
            'course_id' => $data['course_id'],
            'title' => trim($data['title']),
            'category' => $data['category'],
            'due_at' => $data['due_at'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function patchStatus(Request $request, int $id)
    {
        $user = $this->authUser($request);
        $task = Task::query()
            ->whereHas('course', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
        ]);

        $task->update([
            'status' => $data['status'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $this->authUser($request);
        $task = Task::query()
            ->whereHas('course', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);
        $task->delete();

        return response()->json(['ok' => true]);
    }
}
