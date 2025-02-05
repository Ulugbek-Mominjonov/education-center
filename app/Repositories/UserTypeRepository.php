<?php

namespace App\Repositories;

use App\Models\UserType;
use App\Repositories\Interfaces\UserTypeRepositoryInterface;

class UserTypeRepository extends BaseRepository implements UserTypeRepositoryInterface
{
  public function __construct(UserType $userType)
  {
    parent::__construct($userType);
  }
}
