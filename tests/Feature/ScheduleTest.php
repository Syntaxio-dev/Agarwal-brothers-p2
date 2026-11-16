<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    /**
     * Hostinger disables exec/proc_open, so a Schedule::command() event would silently do nothing
     * there (queue never drained, no nightly backup). Every scheduled job must be an in-process closure.
     */
    public function test_every_scheduled_job_runs_inside_the_same_process(): void
    {
        $events = app(Schedule::class)->events();

        $this->assertCount(3, $events);

        foreach ($events as $event) {
            $this->assertInstanceOf(CallbackEvent::class, $event, 'Use Schedule::call(), not Schedule::command().');
            $this->assertNotEmpty($event->description, 'Name every scheduled closure so overlap locking works.');
        }
    }

    public function test_the_mail_queue_is_drained_every_minute(): void
    {
        $queue = collect(app(Schedule::class)->events())->first(fn ($e) => $e->description === 'mail-queue');

        $this->assertNotNull($queue);
        $this->assertSame('* * * * *', $queue->expression);
        $this->assertTrue($queue->withoutOverlapping);
    }
}
