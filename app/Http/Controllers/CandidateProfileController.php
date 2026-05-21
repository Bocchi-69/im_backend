<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CandidateProfileController
{
    /**
     * Get the authenticated candidate's profile
     * GET /api/candidate/profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'candidate') {
            return response()->json([
                'message' => 'Only candidates can access this endpoint',
            ], 403);
        }

        $profile = $user->candidateProfile;

        // Create profile if it doesn't exist
        if (!$profile) {
            $profile = CandidateProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'profile' => $profile,
        ]);
    }

    /**
     * Update the authenticated candidate's profile
     * PUT /api/candidate/profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'candidate') {
            return response()->json([
                'message' => 'Only candidates can access this endpoint',
            ], 403);
        }

        try {
            $validated = $request->validate([
                'job_title'    => ['nullable', 'string', 'max:100'],
                'location'     => ['nullable', 'string', 'max:100'],
                'expected_pay' => ['nullable', 'string', 'max:50'],
                'bio'          => ['nullable', 'string', 'max:1000'],
            ]);

            $profile = $user->candidateProfile;

            if (!$profile) {
                $profile = CandidateProfile::create([
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
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * Upload resume
     * POST /api/candidate/resume
     */
    public function uploadResume(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'candidate') {
            return response()->json([
                'message' => 'Only candidates can access this endpoint',
            ], 403);
        }

        try {
            $request->validate([
                'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
            ]);

            $profile = $user->candidateProfile;

            if (!$profile) {
                $profile = CandidateProfile::create([
                    'user_id' => $user->id,
                ]);
            }

            // Delete old resume if exists
            if ($profile->resume_path) {
                Storage::disk('public')->delete($profile->resume_path);
            }

            // Store new resume
            $path = $request->file('resume')->store('resumes', 'public');

            $profile->update([
                'resume_path' => $path,
            ]);

            return response()->json([
                'message' => 'Resume uploaded successfully',
                'resume_path' => $path,
                'resume_url' => Storage::url($path),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete resume
     * DELETE /api/candidate/resume
     */
    public function deleteResume(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'candidate') {
            return response()->json([
                'message' => 'Only candidates can access this endpoint',
            ], 403);
        }

        $profile = $user->candidateProfile;

        if (!$profile || !$profile->resume_path) {
            return response()->json([
                'message' => 'No resume found',
            ], 404);
        }

        // Delete file from storage
        Storage::disk('public')->delete($profile->resume_path);

        // Update profile
        $profile->update([
            'resume_path' => null,
        ]);

        return response()->json([
            'message' => 'Resume deleted successfully',
        ]);
    }
}