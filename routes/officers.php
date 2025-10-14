<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ========================================
// ELECTORAL OFFICER ROUTES
// ========================================
Route::prefix('officers')->name('officers.')->middleware(['auth.admin', 'role.officer'])->group(function () {
    Route::get('/dashboard', function () {
        $mockOfficerData = [
            'totalVoters' => 500,
            'votedVoters' => 330,
            'electionStatus' => 'active',
            'recentActivity' => [
                ['time' => '10:30 AM', 'action' => 'Vote cast for President', 'voter' => 'Student #12345'],
                ['time' => '10:25 AM', 'action' => 'Vote cast for Vice President', 'voter' => 'Student #12346'],
                ['time' => '10:20 AM', 'action' => 'Vote cast for Secretary', 'voter' => 'Student #12347'],
            ],
        ];

        return Inertia::render('officer/dashboard', $mockOfficerData);
    })->name('dashboard');

    // Election Management (Read-only for Officers)
    Route::get('/candidates', function () {
        $mockCandidatesData = [
            'candidates' => [
                ['id' => 1, 'name' => 'John Doe', 'position' => 'President', 'partylist' => 'Progressive Party', 'votes' => 150],
                ['id' => 2, 'name' => 'Jane Smith', 'position' => 'President', 'partylist' => 'Unity Party', 'votes' => 120],
                ['id' => 3, 'name' => 'Bob Johnson', 'position' => 'President', 'partylist' => 'Independent', 'votes' => 60],
                ['id' => 4, 'name' => 'Alice Brown', 'position' => 'Vice President', 'partylist' => 'Progressive Party', 'votes' => 120],
                ['id' => 5, 'name' => 'Charlie Wilson', 'position' => 'Vice President', 'partylist' => 'Unity Party', 'votes' => 120],
            ],
            'totalCandidates' => 5,
            'positions' => ['President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor'],
        ];

        return Inertia::render('officer/candidates/index', $mockCandidatesData);
    })->name('candidates.index');

    Route::get('/courses', function () {
        $mockCoursesData = [
            'courses' => [
                ['id' => 1, 'name' => 'Bachelor of Science in Information Technology', 'code' => 'BSIT', 'voters' => 150],
                ['id' => 2, 'name' => 'Bachelor of Science in Computer Science', 'code' => 'BSCS', 'voters' => 120],
                ['id' => 3, 'name' => 'Bachelor of Science in Electronics Engineering', 'code' => 'BSEE', 'voters' => 100],
                ['id' => 4, 'name' => 'Bachelor of Science in Civil Engineering', 'code' => 'BSCE', 'voters' => 80],
                ['id' => 5, 'name' => 'Bachelor of Science in Mechanical Engineering', 'code' => 'BSME', 'voters' => 50],
            ],
            'totalCourses' => 5,
            'totalVoters' => 500,
        ];

        return Inertia::render('officer/courses/index', $mockCoursesData);
    })->name('courses.index');

    Route::get('/partylists', function () {
        $mockPartylistsData = [
            'partylists' => [
                ['id' => 1, 'name' => 'Progressive Party', 'acronym' => 'PROG', 'candidates' => 8, 'votes' => 300],
                ['id' => 2, 'name' => 'Unity Party', 'acronym' => 'UNITY', 'candidates' => 6, 'votes' => 250],
                ['id' => 3, 'name' => 'Independent', 'acronym' => 'IND', 'candidates' => 3, 'votes' => 100],
            ],
            'totalPartylists' => 3,
            'totalCandidates' => 17,
        ];

        return Inertia::render('officer/partylists/index', $mockPartylistsData);
    })->name('partylists.index');

    Route::get('/positions', function () {
        $mockPositionsData = [
            'positions' => [
                ['id' => 1, 'name' => 'President', 'description' => 'Chief Executive Officer', 'candidates' => 3, 'votes' => 330],
                ['id' => 2, 'name' => 'Vice President', 'description' => 'Assistant to President', 'candidates' => 2, 'votes' => 240],
                ['id' => 3, 'name' => 'Secretary', 'description' => 'Record Keeper', 'candidates' => 4, 'votes' => 200],
                ['id' => 4, 'name' => 'Treasurer', 'description' => 'Financial Manager', 'candidates' => 2, 'votes' => 160],
                ['id' => 5, 'name' => 'Auditor', 'description' => 'Financial Auditor', 'candidates' => 3, 'votes' => 120],
            ],
            'totalPositions' => 5,
            'totalCandidates' => 14,
        ];

        return Inertia::render('officer/positions/index', $mockPositionsData);
    })->name('positions.index');

    // Voter Management
    Route::get('/voters', function () {
        $mockVotersData = [
            'voters' => [
                ['id' => 1, 'student_number' => '2021-00001', 'name' => 'John Doe', 'course' => 'BSIT', 'year_level' => 4, 'has_voted' => true, 'voted_at' => '2025-01-15 10:30:00'],
                ['id' => 2, 'student_number' => '2021-00002', 'name' => 'Jane Smith', 'course' => 'BSCS', 'year_level' => 4, 'has_voted' => true, 'voted_at' => '2025-01-15 10:25:00'],
                ['id' => 3, 'student_number' => '2021-00003', 'name' => 'Bob Johnson', 'course' => 'BSEE', 'year_level' => 3, 'has_voted' => false, 'voted_at' => null],
                ['id' => 4, 'student_number' => '2021-00004', 'name' => 'Alice Brown', 'course' => 'BSCE', 'year_level' => 3, 'has_voted' => true, 'voted_at' => '2025-01-15 10:20:00'],
                ['id' => 5, 'student_number' => '2021-00005', 'name' => 'Charlie Wilson', 'course' => 'BSME', 'year_level' => 2, 'has_voted' => false, 'voted_at' => null],
            ],
            'totalVoters' => 500,
            'votedVoters' => 330,
            'pendingVoters' => 170,
        ];

        return Inertia::render('officer/voters/index', $mockVotersData);
    })->name('voters.index');

    Route::get('/voters/{voter}', function () {
        return Inertia::render('officer/voters/show');
    })->name('voters.show');

    // Vote Monitoring
    Route::get('/votes', function () {
        $mockVotesData = [
            'votes' => [
                ['id' => 1, 'voter' => 'Student #12345', 'position' => 'President', 'candidate' => 'John Doe', 'timestamp' => '2025-01-15 10:30:00'],
                ['id' => 2, 'voter' => 'Student #12346', 'position' => 'Vice President', 'candidate' => 'Alice Brown', 'timestamp' => '2025-01-15 10:25:00'],
                ['id' => 3, 'voter' => 'Student #12347', 'position' => 'Secretary', 'candidate' => 'Jane Smith', 'timestamp' => '2025-01-15 10:20:00'],
                ['id' => 4, 'voter' => 'Student #12348', 'position' => 'Treasurer', 'candidate' => 'Bob Johnson', 'timestamp' => '2025-01-15 10:15:00'],
                ['id' => 5, 'voter' => 'Student #12349', 'position' => 'Auditor', 'candidate' => 'Charlie Wilson', 'timestamp' => '2025-01-15 10:10:00'],
            ],
            'totalVotes' => 330,
            'recentVotes' => 5,
            'voteDistribution' => [
                ['position' => 'President', 'votes' => 150],
                ['position' => 'Vice President', 'votes' => 120],
                ['position' => 'Secretary', 'votes' => 100],
                ['position' => 'Treasurer', 'votes' => 80],
                ['position' => 'Auditor', 'votes' => 60],
            ],
        ];

        return Inertia::render('officer/votes/index', $mockVotesData);
    })->name('votes.index');

    Route::get('/votes/{vote}', function () {
        return Inertia::render('officer/votes/show');
    })->name('votes.show');

    // Election Monitoring
    Route::get('/elections', function () {
        return Inertia::render('officer/elections/index');
    })->name('elections.index');

    Route::get('/elections/{election}/monitor', function () {
        return Inertia::render('officer/elections/monitor');
    })->name('elections.monitor');

    // Reports (Limited)
    Route::get('/reports', function () {
        return Inertia::render('officer/reports/index');
    })->name('reports.index');

    Route::get('/reports/voting-summary', function () {
        return Inertia::render('officer/reports/voting-summary');
    })->name('reports.voting-summary');

    // Settings
    Route::get('/settings', function () {
        return redirect()->route('officers.settings.profile');
    })->name('settings.index');

    Route::get('/settings/profile', function () {
        return Inertia::render('officer/settings/profile');
    })->name('settings.profile');

    Route::get('/settings/password', function () {
        return Inertia::render('officer/settings/password');
    })->name('settings.password');

});
