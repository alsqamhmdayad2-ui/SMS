<?php

namespace Tests\Unit;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_has_teacher_relationship()
    {
        $user = new User();

        $relation = $user->teacher();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(Teacher::class, $relation->getRelated());
    }
}
