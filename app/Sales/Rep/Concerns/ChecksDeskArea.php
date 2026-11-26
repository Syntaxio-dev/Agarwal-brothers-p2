<?php

namespace App\Sales\Rep\Concerns;

/**
 * For every page of a desk resource: each Livewire request (not only the first load) checks again that the area is still open for the
 * signed-in rep. So a manager who closes an area, or switches the rep off, stops a page that was already open in the browser too.
 */
trait ChecksDeskArea
{
    public function bootChecksDeskArea(): void
    {
        abort_unless(static::getResource()::canAccess(), 403, 'Your manager has not opened this part of the web desk for you.');
    }
}
