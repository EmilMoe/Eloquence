<?php

namespace EmilMoe\Eloquence;

use Illuminate\Database\Eloquent\Model;

class Eloquence extends Model
{
    use TableLock;
    use PropertyObserver;
}
