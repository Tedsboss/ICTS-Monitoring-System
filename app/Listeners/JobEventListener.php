<?php

namespace App\Listeners;

use App\Models\JobRun;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\InteractsWithQueue;

class JobEventListener
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(object $event): void
  {
    //
  }

  /**
   * Handle the JobProcessing event.
   *
   * @param  \Illuminate\Queue\Events\JobProcessing  $event
   * @return void
   */
  public function handleJobProcessing(JobProcessing $event)
  {
    $payload = $event->job->payload();

    JobRun::updateOrCreate(
      ['job_id' => $this->jobId($event), 'job_name' => $event->job->resolveName()],
      [
        'queue'      => $event->job->getQueue(),
        'connection' => $event->connectionName,
        'status'     => 'processing',
        'attempts'   => $payload['attempts'] ?? 0,
        'payload'    => $payload,
        'started_at' => Carbon::now(),
      ]
    );
  }

  /**
   * Handle the JobProcessed event.
   *
   * @param  \Illuminate\Queue\Events\JobProcessed  $event
   * @return void
   */
  public function handleJobProcessed(JobProcessed $event)
  {
    $id   = $this->jobId($event);
    $name = $event->job->resolveName();

    $run = JobRun::where('job_id', $id)->where('job_name', $name)->latest('id')->first();

    $finished = Carbon::now();
    if ($run) {
      $duration = optional($run->started_at)->diffInMilliseconds($finished);
      $run->update([
        'status'      => 'processed',
        'finished_at' => $finished,
        'duration_ms' => $duration,
        'attempts'    => $event->job->payload()['attempts'] ?? $run->attempts,
      ]);
    } else {
      // Edge case: if we never saw Processing (e.g., app restarted)
      JobRun::create([
        'job_id'      => $id,
        'job_name'    => $name,
        'queue'       => $event->job->getQueue(),
        'connection'  => $event->connectionName,
        'status'      => 'processed',
        'started_at'  => null,
        'finished_at' => $finished,
      ]);
    }
  }

  /**
   * Handle the JobFailed event.
   *
   * @param  \Illuminate\Queue\Events\JobFailed  $event
   * @return void
   */
  public function handleJobFailed(JobFailed $event)
  {
    $id   = $this->jobId($event);
    $name = $event->job->resolveName();

    $run = JobRun::where('job_id', $id)->where('job_name', $name)->latest('id')->first();
    $finished = Carbon::now();
    $data = [
      'status'      => 'failed',
      'finished_at' => $finished,
      'duration_ms' => optional($run->started_at)->diffInMilliseconds($finished),
      'attempts'    => $event->job->payload()['attempts'] ?? $run->attempts ?? 0,
      'exception'   => (string) $event->exception,
    ];

    if ($run) {
      $run->update($data);
    } else {
      JobRun::create(array_merge([
        'job_id'     => $id,
        'job_name'   => $name,
        'queue'      => $event->job->getQueue(),
        'connection' => $event->connectionName,
      ], $data));
    }
  }

  /**
   * Safely extract a stable job id across drivers
   *
   * @param  object $event
   * @return string|null
   */
  protected function jobId($event): ?string
  {
    // Redis/SQS expose getJobId(); DB driver may not.
    if (method_exists($event->job, 'getJobId')) {
      return $event->job->getJobId();
    }
    $payload = $event->job->payload();
    return $payload['uuid'] ?? $payload['id'] ?? null;
  }
}
