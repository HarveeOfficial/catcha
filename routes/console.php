<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('boats:expire')->daily();
