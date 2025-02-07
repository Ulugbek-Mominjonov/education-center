<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;

class StudentsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Student::selectRaw("
          students.id, 
          students.first_name, 
          students.last_name, 
          students.phone,
          string_agg(groups.name, ', ' ORDER BY groups.name) as groups,
          string_agg(subjects.name, ', ' ORDER BY subjects.name) as subjects,
          string_agg(CONCAT(teachers.first_name, ' ', teachers.last_name), ', ' ORDER BY teachers.first_name) as teachers
      ")
            ->leftJoin('group_student', 'students.id', '=', 'group_student.student_id')
            ->leftJoin('groups', 'group_student.group_id', '=', 'groups.id')
            ->leftJoin('subjects', 'groups.subject_id', '=', 'subjects.id')
            ->leftJoin('group_teacher', 'groups.id', '=', 'group_teacher.group_id')
            ->leftJoin('teachers', 'group_teacher.teacher_id', '=', 'teachers.id')
            ->groupBy('students.id', 'students.first_name', 'students.last_name', 'students.phone')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Last Name',
            'Phone',
            'Groups',
            'Subjects',
            'Teachers',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 50,
            'F' => 50,
            'G' => 50,
        ];
    }

    public function styles($sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center'], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'D9D9D9']]],
        ];
    }
}
