<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVoteReceiptEmailJob implements ShouldQueue
{
    use FoundationQueueable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $email,
        private readonly array $receiptData,
        private readonly array $requestInfo = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $template = 'email-templates.vote-receipt';
            $subject = 'Your Vote Receipt - '.$this->receiptData['election_name'];

            // Map receipt data to template variable names
            $data = [
                'electionName' => $this->receiptData['election_name'] ?? 'Election',
                'voteRef' => $this->receiptData['vote_ref'] ?? 'N/A',
                'voterName' => $this->receiptData['voter_name'] ?? 'Unknown',
                'studentNumber' => $this->receiptData['student_number'] ?? 'N/A',
                'courseName' => $this->receiptData['course_name'] ?? 'N/A',
                'votedAt' => $this->receiptData['voted_at'] ?? now()->format('M d, Y \a\t H:i:s'),
                'votes' => $this->receiptData['votes'] ?? [],
                'ipAddress' => $this->requestInfo['ip_address'] ?? request()->ip(),
            ];

            Mail::send($template, $data, function ($message) use ($subject) {
                $message->to($this->email)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address', 'admin@ehalal.tech'),
                        config('mail.from.name', 'E-Halal BTECHenyo Admin')
                    );
            });

            Log::info('Vote receipt email sent successfully', [
                'email' => $this->email,
                'vote_ref' => $this->receiptData['vote_ref'] ?? 'unknown',
                'election_name' => $this->receiptData['election_name'] ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send vote receipt email', [
                'email' => $this->email,
                'vote_ref' => $this->receiptData['vote_ref'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Vote receipt email job failed permanently', [
            'email' => $this->email,
            'vote_ref' => $this->receiptData['vote_ref'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
