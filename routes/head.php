<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ========================================
// ELECTORAL HEAD ROUTES
// ========================================
Route::prefix('head')->name('head.')->middleware(['auth.admin', 'role.head'])->group(function () {
    Route::get('/dashboard', function () {
        $totalVoters = \App\Models\Voter::count();
        $votedVoters = \App\Models\Voter::where('has_voted', true)->count();
        $totalCandidates = \App\Models\Candidate::count();
        $totalPositions = \App\Models\Position::count();

        // Get current election status
        $currentElection = \App\Models\ElectionStatus::first();
        $electionStatus = $currentElection?->status ?? 'idle';

        // Get vote distribution by position
        $voteDistribution = \App\Models\Position::with('candidates')
            ->get()
            ->map(function ($position) {
                return [
                    'position' => $position->description,
                    'votes' => $position->candidates->sum('votes'),
                    'candidates_count' => $position->candidates->count(),
                ];
            })
            ->toArray();

        return Inertia::render('head/dashboard', [
            'totalVoters' => $totalVoters,
            'votedVoters' => $votedVoters,
            'totalCandidates' => $totalCandidates,
            'totalPositions' => $totalPositions,
            'electionStatus' => $electionStatus,
            'voteDistribution' => $voteDistribution,
            'electionName' => $currentElection?->election_name ?? 'No Active Election',
        ]);
    })->name('dashboard');

    // Election Management
    Route::get('/elections', function () {
        $elections = \App\Models\ElectionHistory::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->election_name,
                    'status' => $election->status,
                    'end_time' => $election->end_time?->toISOString(),
                    'control_number' => $election->control_number,
                    'details_pdf' => $election->details_pdf,
                    'results_pdf' => $election->results_pdf,
                    'created_at' => $election->created_at?->toISOString(),
                ];
            });

        return Inertia::render('head/elections/index', [
            'elections' => $elections,
        ]);
    })->name('elections.index');

    Route::get('/elections/create', function () {
        return Inertia::render('head/elections/create');
    })->name('elections.create');

    Route::get('/elections/{election}/edit', function () {
        return Inertia::render('head/elections/edit');
    })->name('elections.edit');

    Route::post('/elections', function () {
        // Handle election creation
    })->name('elections.store');

    Route::put('/elections/{election}', function () {
        // Handle election update
    })->name('elections.update');

    Route::delete('/elections/{election}', function () {
        // Handle election deletion
    })->name('elections.destroy');

    // Candidate Management
    Route::get('/candidates', function () {
        $candidates = \App\Models\Candidate::with(['position', 'partylist'])
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'firstname' => $candidate->firstname,
                    'lastname' => $candidate->lastname,
                    'position' => $candidate->position?->description,
                    'position_id' => $candidate->position_id,
                    'partylist' => $candidate->partylist?->name,
                    'partylist_id' => $candidate->partylist_id,
                    'photo' => $candidate->photo,
                    'platform' => $candidate->platform,
                    'votes' => $candidate->votes ?? 0,
                    'created_at' => $candidate->created_at?->toISOString(),
                    'updated_at' => $candidate->updated_at?->toISOString(),
                ];
            });

        $positions = \App\Models\Position::orderBy('priority')->get();
        $partylists = \App\Models\Partylist::all();

        return Inertia::render('head/candidates/index', [
            'candidates' => $candidates,
            'positions' => $positions,
            'partylists' => $partylists,
        ]);
    })->name('candidates.index');

    Route::get('/candidates/create', function () {
        return Inertia::render('head/candidates/create');
    })->name('candidates.create');

    Route::get('/candidates/{candidate}/edit', function () {
        return Inertia::render('head/candidates/edit');
    })->name('candidates.edit');

    Route::post('/candidates', [\App\Http\Controllers\Head\CandidateController::class, 'store'])->name('candidates.store');
    Route::put('/candidates/{candidate}', [\App\Http\Controllers\Head\CandidateController::class, 'update'])->name('candidates.update');
    Route::delete('/candidates/{candidate}', [\App\Http\Controllers\Head\CandidateController::class, 'destroy'])->name('candidates.destroy');

    // Position Management
    Route::get('/positions', function () {
        $positions = \App\Models\Position::withCount('candidates')
            ->orderBy('priority')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->description,
                    'description' => $position->description,
                    'max_winners' => $position->max_vote,
                    'priority' => $position->priority,
                    'candidates_count' => $position->candidates_count,
                    'status' => 'active', // Default status for now
                    'created_at' => $position->created_at?->toISOString(),
                    'updated_at' => $position->updated_at?->toISOString(),
                ];
            });

        return Inertia::render('head/positions/index', [
            'positions' => $positions,
        ]);
    })->name('positions.index');

    Route::post('/positions', [\App\Http\Controllers\Head\PositionController::class, 'store'])->name('positions.store');
    Route::put('/positions/{position}', [\App\Http\Controllers\Head\PositionController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{position}', [\App\Http\Controllers\Head\PositionController::class, 'destroy'])->name('positions.destroy');
    Route::get('/positions/{position}/candidates', [\App\Http\Controllers\Head\PositionController::class, 'candidates'])->name('positions.candidates');
    Route::post('/positions/reorder', [\App\Http\Controllers\Head\PositionController::class, 'reorder'])->name('positions.reorder');

    // Party List Management
    Route::get('/partylists', function () {
        $partylists = \App\Models\Partylist::withCount('candidates')
            ->get()
            ->map(function ($partylist) {
                return [
                    'id' => $partylist->id,
                    'name' => $partylist->name,
                    'color' => $partylist->color,
                    'platform' => $partylist->platform,
                    'candidates_count' => $partylist->candidates_count,
                    'created_at' => $partylist->created_at?->toISOString(),
                    'updated_at' => $partylist->updated_at?->toISOString(),
                ];
            });

        return Inertia::render('head/partylists/index', [
            'partylists' => $partylists,
        ]);
    })->name('partylists.index');

    Route::post('/partylists', [\App\Http\Controllers\Head\PartylistController::class, 'store'])->name('partylists.store');
    Route::put('/partylists/{partylist}', [\App\Http\Controllers\Head\PartylistController::class, 'update'])->name('partylists.update');
    Route::delete('/partylists/{partylist}', [\App\Http\Controllers\Head\PartylistController::class, 'destroy'])->name('partylists.destroy');
    Route::get('/partylists/{partylist}/candidates', [\App\Http\Controllers\Head\PartylistController::class, 'candidates'])->name('partylists.candidates');

    // Voter Management
    Route::get('/voters', function () {
        $voters = \App\Models\Voter::with('course')
            ->get()
            ->map(function ($voter) {
                return [
                    'id' => $voter->id,
                    'student_number' => $voter->student_number,
                    'course' => $voter->course?->description,
                    'course_id' => $voter->course_id,
                    'has_voted' => $voter->has_voted,
                    'created_at' => $voter->created_at?->toISOString(),
                    'updated_at' => $voter->updated_at?->toISOString(),
                ];
            });

        $courses = \App\Models\Course::all();
        $totalVoters = $voters->count();
        $votedCount = $voters->where('has_voted', true)->count();

        return Inertia::render('head/voters/index', [
            'voters' => $voters,
            'courses' => $courses,
            'totalVoters' => $totalVoters,
            'votedCount' => $votedCount,
        ]);
    })->name('voters.index');

    Route::post('/voters', [\App\Http\Controllers\Head\VoterController::class, 'store'])->name('voters.store');
    Route::put('/voters/{voter}', [\App\Http\Controllers\Head\VoterController::class, 'update'])->name('voters.update');
    Route::delete('/voters/{voter}', [\App\Http\Controllers\Head\VoterController::class, 'destroy'])->name('voters.destroy');
    Route::get('/voters/template/download', [\App\Http\Controllers\Head\VoterController::class, 'downloadTemplate'])->name('voters.template');
    Route::post('/voters/import', [\App\Http\Controllers\Head\VoterController::class, 'importCsv'])->name('voters.import');

    // Course Management
    Route::get('/courses', function () {
        $courses = \App\Models\Course::withCount('voters')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'code' => $course->code,
                    'description' => $course->description,
                    'voters_count' => $course->voters_count,
                    'created_at' => $course->created_at?->toISOString(),
                    'updated_at' => $course->updated_at?->toISOString(),
                ];
            });

        return Inertia::render('head/courses/index', [
            'courses' => $courses,
        ]);
    })->name('courses.index');

    Route::post('/courses', [\App\Http\Controllers\Head\CourseController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [\App\Http\Controllers\Head\CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [\App\Http\Controllers\Head\CourseController::class, 'destroy'])->name('courses.destroy');
    Route::get('/courses/{course}/voters', [\App\Http\Controllers\Head\CourseController::class, 'voters'])->name('courses.voters');

    // Vote Management
    Route::get('/votes', function () {
        $totalVoters = \App\Models\Voter::count();
        $totalVoted = \App\Models\Voter::where('has_voted', true)->count();
        $totalPositions = \App\Models\Position::count();
        $totalCandidates = \App\Models\Candidate::count();
        $totalPartylists = \App\Models\Partylist::count();

        $votingRate = $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 1) : 0;

        // Get vote distribution by position
        $voteDistribution = \App\Models\Position::with('candidates')
            ->orderBy('priority')
            ->get()
            ->map(function ($position) {
                return [
                    'position' => $position->description,
                    'votes' => $position->candidates->sum('votes'),
                    'candidates' => $position->candidates->count(),
                ];
            });

        // Get candidate vote data
        $candidateVoteData = \App\Models\Candidate::with('position')
            ->get()
            ->map(function ($candidate) {
                return [
                    'name' => $candidate->firstname.' '.$candidate->lastname,
                    'position' => $candidate->position?->description ?? 'Unknown',
                    'votes' => $candidate->votes ?? 0,
                ];
            });

        return Inertia::render('head/votes/index', [
            'voteDistribution' => $voteDistribution,
            'candidateVoteData' => $candidateVoteData,
            'electionStats' => [
                'totalVoters' => $totalVoters,
                'totalVoted' => $totalVoted,
                'votingRate' => $votingRate,
                'totalPositions' => $totalPositions,
                'totalCandidates' => $totalCandidates,
                'totalPartylists' => $totalPartylists,
            ],
        ]);
    })->name('votes.index');

    Route::get('/voters/import', function () {
        return Inertia::render('head/voters/import');
    })->name('voters.import');

    Route::post('/voters/import', function () {
        // Handle voter import
    })->name('voters.import.store');

    // Officer Management
    Route::get('/officers', function () {
        $officers = \App\Models\Admin::where('role', 'officer')
            ->orderBy('created_on', 'desc')
            ->get()
            ->map(function ($officer) {
                return [
                    'id' => $officer->id,
                    'username' => $officer->username,
                    'email' => $officer->email,
                    'firstname' => $officer->firstname,
                    'lastname' => $officer->lastname,
                    'photo' => $officer->photo,
                    'role' => $officer->role,
                    'gender' => $officer->gender,
                    'created_at' => $officer->created_on?->format('Y-m-d\TH:i:s\Z'),
                ];
            });

        return Inertia::render('head/officers/index', [
            'officers' => $officers,
        ]);
    })->name('officers.index');

    Route::post('/officers', [\App\Http\Controllers\Head\OfficerController::class, 'store'])->name('officers.store');

    // Results & Reports
    Route::get('/results', function () {
        $mockResultsData = [
            ['position' => 'President', 'candidate' => 'John Doe', 'votes' => 150, 'percentage' => 45.5],
            ['position' => 'President', 'candidate' => 'Jane Smith', 'votes' => 120, 'percentage' => 36.4],
            ['position' => 'President', 'candidate' => 'Bob Johnson', 'votes' => 60, 'percentage' => 18.1],
            ['position' => 'Vice President', 'candidate' => 'Alice Brown', 'votes' => 120, 'percentage' => 50.0],
            ['position' => 'Vice President', 'candidate' => 'Charlie Wilson', 'votes' => 120, 'percentage' => 50.0],
        ];

        return Inertia::render('head/results/index', [
            'resultsData' => $mockResultsData,
            'electionTitle' => 'BTECHenyo Student Council Election 2025',
            'totalVoters' => 500,
            'votedVoters' => 330,
        ]);
    })->name('results.index');

    Route::get('/results/{election}', function () {
        return Inertia::render('head/results/show');
    })->name('results.show');

    Route::get('/reports', function () {
        return Inertia::render('head/reports/index');
    })->name('reports.index');

    Route::get('/reports/election-summary', function () {
        return Inertia::render('head/reports/election-summary');
    })->name('reports.election-summary');

    Route::get('/reports/voting-statistics', function () {
        return Inertia::render('head/reports/voting-statistics');
    })->name('reports.voting-statistics');

    // System Management
    Route::get('/settings', function () {
        return redirect()->route('head.settings.profile');
    })->name('settings.index');

    // Election Setup
    Route::get('/setup', function () {
        return Inertia::render('head/setup/index');
    })->name('setup.index');

    // System Configuration
    Route::get('/configure', function () {
        $mockConfigData = [
            'electionSettings' => [
                'electionName' => 'BTECHenyo Student Council Election 2025',
                'startDate' => '2025-01-15',
                'endDate' => '2025-01-17',
                'isActive' => true,
                'maxVotesPerPosition' => 1,
                'allowAbstain' => true,
            ],
            'systemSettings' => [
                'otpExpiry' => 5, // minutes
                'sessionTimeout' => 30, // minutes
                'maxLoginAttempts' => 3,
                'enableEmailNotifications' => true,
            ],
            'positions' => [
                ['name' => 'President', 'description' => 'Chief Executive Officer', 'maxCandidates' => 1],
                ['name' => 'Vice President', 'description' => 'Assistant to President', 'maxCandidates' => 1],
                ['name' => 'Secretary', 'description' => 'Record Keeper', 'maxCandidates' => 1],
                ['name' => 'Treasurer', 'description' => 'Financial Manager', 'maxCandidates' => 1],
                ['name' => 'Auditor', 'description' => 'Financial Auditor', 'maxCandidates' => 1],
            ],
        ];

        return Inertia::render('head/configure/index', $mockConfigData);
    })->name('configure.index');

    Route::get('/settings/profile', function () {
        return Inertia::render('head/settings/profile');
    })->name('settings.profile');

    Route::get('/settings/password', function () {
        return Inertia::render('head/settings/password');
    })->name('settings.password');

    Route::put('/settings', function () {
        // Handle settings update
    })->name('settings.update');

    Route::get('/logs', function (Request $request) {
        $perPage = 10;
        $query = \App\Models\AdminLog::with('admin');

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action_description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('admin', function ($adminQuery) use ($searchTerm) {
                        $adminQuery->where('firstname', 'like', "%{$searchTerm}%")
                            ->orWhere('lastname', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Apply action filter
        if ($request->filled('action')) {
            $query->where('action_type', $request->get('action'));
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                    'user_id' => $log->user_id,
                    'role' => $log->role,
                    'action' => $log->action_description,
                    'action_type' => $log->action_type,
                    'model_type' => $log->model_type ? class_basename($log->model_type) : null,
                    'ip_address' => $log->ip_address,
                    'admin_name' => $log->admin ? "{$log->admin->firstname} {$log->admin->lastname}" : 'System',
                ];
            });

        return Inertia::render('head/logs/index', [
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
                'has_more_pages' => $logs->hasMorePages(),
            ],
        ]);
    })->name('logs.index');

    Route::get('/ballots', function () {
        $positions = \App\Models\Position::withCount('candidates')
            ->orderBy('priority')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->description,
                    'description' => $position->description,
                    'max_winners' => $position->max_vote,
                    'max_vote' => $position->max_vote,
                    'priority' => $position->priority,
                    'candidates_count' => $position->candidates_count,
                    'created_at' => $position->created_at?->toISOString(),
                    'updated_at' => $position->updated_at?->toISOString(),
                ];
            });

        $candidates = \App\Models\Candidate::with(['position', 'partylist'])
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'firstname' => $candidate->firstname,
                    'lastname' => $candidate->lastname,
                    'position' => $candidate->position?->description,
                    'position_id' => $candidate->position_id,
                    'partylist' => $candidate->partylist?->name,
                    'partylist_id' => $candidate->partylist_id,
                    'photo' => $candidate->photo,
                    'platform' => $candidate->platform,
                    'votes' => $candidate->votes ?? 0,
                    'created_at' => $candidate->created_at?->toISOString(),
                    'updated_at' => $candidate->updated_at?->toISOString(),
                ];
            });

        return Inertia::render('head/ballots/index', [
            'positions' => $positions,
            'candidates' => $candidates,
        ]);
    })->name('ballots.index');

    // OTP Testing (Development only)
    Route::get('/otp-test', [App\Http\Controllers\Admin\OtpTestController::class, 'index'])
        ->name('otp-test.index');

    Route::post('/otp-test/send', [App\Http\Controllers\Admin\OtpTestController::class, 'sendTestOtp'])
        ->name('otp-test.send');

    Route::post('/otp-test/verify', [App\Http\Controllers\Admin\OtpTestController::class, 'verifyTestOtp'])
        ->name('otp-test.verify');

    Route::get('/otp-test/template/{template}', [App\Http\Controllers\Admin\OtpTestController::class, 'testEmailTemplate'])
        ->name('otp-test.template');

    Route::post('/otp-test/vote-receipt', [App\Http\Controllers\Admin\OtpTestController::class, 'sendTestVoteReceipt'])
        ->name('otp-test.vote-receipt');

    Route::post('/otp-test/password-reset', [App\Http\Controllers\Admin\OtpTestController::class, 'sendTestPasswordReset'])
        ->name('otp-test.password-reset');

    Route::post('/otp-test/toggle', [App\Http\Controllers\Admin\OtpTestController::class, 'toggleOtpLogin'])
        ->name('otp-test.toggle');

    // Logout
    Route::post('/logout', function () {
        // Handle head logout
    })->name('logout');
});
