<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ========================================
// VOTER ROUTES
// ========================================
Route::prefix('voters')->name('voters.')->middleware('auth.voter')->group(function () {
    // Voting Interface (Development - No Auth)
    Route::get('/vote', function () {
        // Mock data for development
        return Inertia::render('voters/vote', [
            'auth' => [
                'user' => [
                    'student_number' => '2024-00001',
                    'name' => 'Juan Dela Cruz',
                ],
            ],
            'positions' => [
                [
                    'id' => 1,
                    'name' => 'President',
                    'max_vote' => 1,
                    'candidates' => [
                        [
                            'id' => 1,
                            'name' => 'John Doe',
                            'party' => 'Progressive Party',
                            'photo' => null,
                            'platform' => 'I promise to advocate for better facilities and student welfare programs.',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Jane Smith',
                            'party' => 'Unity Party',
                            'photo' => null,
                            'platform' => 'Together we can build a stronger student community with transparency and accountability.',
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Vice President',
                    'max_vote' => 1,
                    'candidates' => [
                        [
                            'id' => 3,
                            'name' => 'Mike Johnson',
                            'party' => 'Progressive Party',
                            'photo' => null,
                            'platform' => 'Supporting the president\'s vision while ensuring student voices are heard.',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Sarah Williams',
                            'party' => 'Unity Party',
                            'photo' => null,
                            'platform' => 'Dedicated to bridging gaps between students and administration.',
                        ],
                    ],
                ],
                [
                    'id' => 3,
                    'name' => 'Board Members',
                    'max_vote' => 3,
                    'candidates' => [
                        [
                            'id' => 5,
                            'name' => 'Alice Brown',
                            'party' => 'Progressive Party',
                            'photo' => null,
                            'platform' => 'Focus on academic excellence and student development.',
                        ],
                        [
                            'id' => 6,
                            'name' => 'Bob Davis',
                            'party' => 'Unity Party',
                            'photo' => null,
                            'platform' => 'Promoting inclusive policies for all students.',
                        ],
                        [
                            'id' => 7,
                            'name' => 'Carol White',
                            'party' => 'Independent',
                            'photo' => null,
                            'platform' => 'Working for environmental sustainability on campus.',
                        ],
                        [
                            'id' => 8,
                            'name' => 'David Green',
                            'party' => 'Progressive Party',
                            'photo' => null,
                            'platform' => 'Championing student rights and welfare.',
                        ],
                        [
                            'id' => 9,
                            'name' => 'Emma Taylor',
                            'party' => 'Unity Party',
                            'photo' => null,
                            'platform' => 'Supporting mental health and wellness programs.',
                        ],
                    ],
                ],
            ],
            'election' => [
                'id' => 1,
                'name' => 'Student Council Election 2024',
            ],
            'has_voted' => false,
        ]);
    })->name('vote');

    Route::post('/vote/submit', function () {
        // Mock vote submission - returns success
        return redirect()->route('voters.vote')->with([
            'has_voted' => true,
            'vote_ref' => 'VOTE-'.date('ymd').'-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    })->name('vote.submit');

    Route::get('/receipt', function () {
        return Inertia::render('voters/receipt/index');
    })->name('receipt.lookup');

    Route::get('/receipt/{slug}', function ($slug) {
        // Mock receipt data
        return Inertia::render('voters/receipt/[slug]', [
            'vote_ref' => $slug,
            'election' => [
                'name' => 'Student Council Election 2024',
                'date' => now()->toISOString(),
            ],
            'voter' => [
                'student_number' => '2024-00001',
                'name' => 'Juan Dela Cruz',
                'course' => 'Bachelor of Science in Information Technology',
            ],
            'votes' => [
                [
                    'position' => 'President',
                    'candidate' => 'John Doe',
                    'party' => 'Progressive Party',
                ],
                [
                    'position' => 'Vice President',
                    'candidate' => 'Mike Johnson',
                    'party' => 'Progressive Party',
                ],
                [
                    'position' => 'Board Members',
                    'candidate' => 'Alice Brown',
                    'party' => 'Progressive Party',
                ],
                [
                    'position' => 'Board Members',
                    'candidate' => 'Carol White',
                    'party' => 'Independent',
                ],
                [
                    'position' => 'Board Members',
                    'candidate' => 'Emma Taylor',
                    'party' => 'Unity Party',
                ],
            ],
            'timestamp' => now()->toISOString(),
        ]);
    })->name('receipt');

    Route::get('/dashboard', function () {
        return Inertia::render('voters/dashboard');
    })->name('dashboard');
});
