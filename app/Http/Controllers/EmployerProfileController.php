<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployerProfile;
use App\Models\CandidateProfile;
use Illuminate\Validation\ValidationException;

class EmployerProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'employer') {
            return response()->json([
                'message' => 'Only employers can access this endpoint',
            ], 403);
        }

        $profile = $user->employerProfile;

        if (!$profile) {
            $profile = EmployerProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'profile' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'employer') {
            return response()->json([
                'message' => 'Only employers can access this endpoint',
            ], 403);
        }

        try {
            $validated = $request->validate([
                'company_name' => ['nullable', 'string', 'max:100'],
                'industry' => ['nullable', 'string', 'max:100'],
                'company_size' => ['nullable', 'string', 'max:50'],
                'website' => ['nullable', 'url', 'max:255'],
                'location' => ['nullable', 'string', 'max:100'],
                'about' => ['nullable', 'string', 'max:1000'],
            ]);

            $profile = $user->employerProfile;

            if (!$profile) {
                $profile = EmployerProfile::create([
                    'user_id' => $user->id,
                ]);
            }

            $profile->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => $profile,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function browseCandidates(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'employer') {
            return response()->json([
                'message' => 'Only employers can access this endpoint',
            ], 403);
        }

        $candidates = CandidateProfile::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'candidate');
            })
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->user->id,
                    'name' => $profile->user->name,
                    'email' => $profile->user->email,
                    'job_title' => $profile->job_title,
                    'location' => $profile->location,
                    'expected_pay' => $profile->expected_pay,
                    'bio' => $profile->bio,
                    'has_resume' => !empty($profile->resume_path),
                ];
            });

        return response()->json([
            'candidates' => $candidates,
        ]);
    }
}
