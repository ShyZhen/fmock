<?php

namespace App\Observers;

use App\Models\Answer;

class AnswerObserver
{
    /**
     * Handle the answer "created" event.
     *
     * @param Answer $answer
     *
     * @return void
     */
    public function created(Answer $answer)
    {
        //
    }

    /**
     * Handle the answer "updated" event.
     *
     * @param Answer $answer
     *
     * @return void
     */
    public function updated(Answer $answer)
    {
        //
    }

    /**
     * Handle the answer "deleted" event.
     *
     * @param Answer $answer
     *
     * @return void
     */
    public function deleted(Answer $answer)
    {
        //
    }

    /**
     * Handle the answer "restored" event.
     *
     * @param Answer $answer
     *
     * @return void
     */
    public function restored(Answer $answer)
    {
        //
    }

    /**
     * Handle the answer "force deleted" event.
     *
     * @param Answer $answer
     *
     * @return void
     */
    public function forceDeleted(Answer $answer)
    {
        //
    }
}
