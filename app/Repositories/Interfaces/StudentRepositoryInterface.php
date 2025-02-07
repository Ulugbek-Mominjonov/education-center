<?php

namespace App\Repositories\Interfaces;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
  public function attachUser($request, $id);
  public function changeGroups($request, $id);

  public function assessingStudentTasks($request, $id);

  public function studentsRatings($request);

  public function generateStudentsCsvFile($request);
  public function downloadLatestExcelFile();
}
