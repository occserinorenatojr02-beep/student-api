<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


$students = [
    [
        'id' => 1,
        'student_number' => '2024-0001',
        'name' => 'Juan Dela Cruz',
        'course' => 'Bachelor of Science in Information Technology',
        'year_level' => 2,
        'section' => 'BSIT-2A',
        'email' => 'juan.delacruz@example.com',
        'phone' => '09123456789',
        'status' => 'Active',
        'gender' => 'Male',
        'birth_date' => '2004-05-15',
        'address' => 'Cagayan de Oro City',
        'guardian' => 'Maria Dela Cruz',
        'guardian_phone' => '09187654321',
    ],

];
$overridesPath = storage_path('app/student_overrides.json');

$overrides = file_exists($overridesPath)
    ? (json_decode(file_get_contents($overridesPath), true) ?: [])
    : [];

$students = array_map(
    fn ($s) => array_merge($s, $overrides[$s['id']] ?? []),
    $students
);


/*
|--------------------------------------------------------------------------
| STUDENT LIST
|--------------------------------------------------------------------------
*/

Route::get('/students', function () use ($students) {
    return response()->json($students);
});


/*
|--------------------------------------------------------------------------
| SINGLE STUDENT
|--------------------------------------------------------------------------
*/

Route::get('/students/{id}', function ($id) use ($students) {

    $student = collect($students)
        ->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json([
            'message' => 'Student not found'
        ], 404);
    }

    return response()->json($student);
});


/*
|--------------------------------------------------------------------------
| UPDATE STUDENT PROFILE
|--------------------------------------------------------------------------
|   PUT /api/students/{id}
|--------------------------------------------------------------------------
*/

Route::put('/students/{id}', function (Request $request, $id) use ($students, $overridesPath) {

    $student = collect($students)->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    // student_number is intentionally not editable
    $data = $request->validate([
        'name'           => 'sometimes|required|string|max:100',
        'email'          => 'sometimes|required|email|max:100',
        'course'         => 'sometimes|required|string|max:150',
        'year_level'     => 'sometimes|required|integer|min:1|max:6',
        'phone'          => 'sometimes|nullable|string|max:20',
        'address'        => 'sometimes|nullable|string|max:200',
        'guardian'       => 'sometimes|nullable|string|max:100',
        'guardian_phone' => 'sometimes|nullable|string|max:20',
    ]);

    // Load current saved edits, merge the new ones, write back
    $saved = file_exists($overridesPath)
        ? (json_decode(file_get_contents($overridesPath), true) ?: [])
        : [];

    $saved[$student['id']] = array_merge($saved[$student['id']] ?? [], $data);

    file_put_contents($overridesPath, json_encode($saved, JSON_PRETTY_PRINT), LOCK_EX);

    return response()->json(array_merge($student, $data));
});


/*
|--------------------------------------------------------------------------
| COURSES (subjects)
|--------------------------------------------------------------------------
*/

Route::get('/courses', function () {

    // NOTE: sample descriptions. Replace with your real course outlines.
    return response()->json([
        [
            'id' => 1,
            'code' => 'IT 201',
            'name' => 'Web Development',
            'units' => 3,
            'instructor' => 'Prof. Santos',
            'prerequisite' => 'None',
            'description' => 'Introduces the fundamentals of building websites. Students learn HTML, CSS and JavaScript, responsive layouts, and how to publish a simple, working web page.',
        ],
        [
            'id' => 2,
            'code' => 'IT 202',
            'name' => 'Database Management',
            'units' => 3,
            'instructor' => 'Prof. Reyes',
            'prerequisite' => 'None',
            'description' => 'Covers database design and management. Topics include the relational model, entity-relationship diagrams, normalization, SQL queries, and basic database administration.',
        ],
        [
            'id' => 3,
            'code' => 'IT 203',
            'name' => 'Mobile Application Development',
            'units' => 3,
            'instructor' => 'Prof. Cruz',
            'prerequisite' => 'IT 201',
            'description' => 'Teaches students to design and build mobile apps. Covers screen layouts, navigation, working with APIs, and testing apps on real devices.',
        ],
        [
            'id' => 4,
            'code' => 'IT 204',
            'name' => 'Computer Networking',
            'units' => 3,
            'instructor' => 'Prof. Garcia',
            'prerequisite' => 'None',
            'description' => 'Explains how computer networks work. Includes the OSI and TCP/IP models, IP addressing, routing and switching, and basic network setup and troubleshooting.',
        ],
        [
            'id' => 5,
            'code' => 'IT 205',
            'name' => 'Systems Analysis and Design',
            'units' => 3,
            'instructor' => 'Prof. Mendoza',
            'prerequisite' => 'IT 202',
            'description' => 'Focuses on planning and designing information systems. Students gather requirements, model processes and data, and prepare system documentation.',
        ],
        [
            'id' => 6,
            'code' => 'IT 206',
            'name' => 'Object-Oriented Programming',
            'units' => 3,
            'instructor' => 'Prof. Flores',
            'prerequisite' => 'None',
            'description' => 'Introduces object-oriented concepts such as classes, objects, inheritance, polymorphism and encapsulation, applied through hands-on programming exercises.',
        ],
        [
            'id' => 7,
            'code' => 'IT 207',
            'name' => 'Data Structures and Algorithms',
            'units' => 3,
            'instructor' => 'Prof. Torres',
            'prerequisite' => 'IT 206',
            'description' => 'Studies how data is organized and processed efficiently. Covers arrays, linked lists, stacks, queues, trees, sorting and searching algorithms.',
        ],
        [
            'id' => 8,
            'code' => 'IT 208',
            'name' => 'Human-Computer Interaction',
            'units' => 3,
            'instructor' => 'Prof. Ramirez',
            'prerequisite' => 'IT 201',
            'description' => 'Looks at how people interact with software. Students learn usability principles, user research, wireframing and prototyping of user-friendly interfaces.',
        ],
        [
            'id' => 9,
            'code' => 'IT 209',
            'name' => 'Information Assurance and Security',
            'units' => 3,
            'instructor' => 'Prof. Villanueva',
            'prerequisite' => 'IT 204',
            'description' => 'Covers the basics of protecting information systems. Topics include common threats, authentication, encryption, secure coding practices and security policies.',
        ],
        [
            'id' => 10,
            'code' => 'IT 210',
            'name' => 'Integrative Programming and Technologies',
            'units' => 3,
            'instructor' => 'Prof. Aquino',
            'prerequisite' => 'IT 203',
            'description' => 'Teaches how to combine different systems and technologies. Students build applications that connect a mobile or web front end to a back-end API and database.',
        ],
    ]);

});


/*
|--------------------------------------------------------------------------
| PROGRAMS (degree programs offered)
|--------------------------------------------------------------------------
*/

Route::get('/programs', function () {

    return response()->json([
        [
            'id' => 1,
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'department' => 'Information Technology',
            'duration' => '4 Years',
        ],
        [
            'id' => 2,
            'code' => 'BSBA',
            'name' => 'Bachelor of Science in Business Administration',
            'department' => 'Business Administration',
            'duration' => '4 Years',
        ],
        [
            'id' => 3,
            'code' => 'BSHM',
            'name' => 'Bachelor of Science in Hospitality Management',
            'department' => 'Hospitality Management',
            'duration' => '4 Years',
        ],
        [
            'id' => 4,
            'code' => 'BEED',
            'name' => 'Bachelor of Elementary Education',
            'department' => 'Education',
            'duration' => '4 Years',
        ],
    ]);

});


/*
|--------------------------------------------------------------------------
| SCHEDULES
|--------------------------------------------------------------------------
*/

Route::get('/schedules', function () {

    return response()->json([
        [
            'id' => 1,
            'day' => 'Monday',
            'time' => '8:00 AM - 10:00 AM',
            'subject' => 'Web Development',
            'code' => 'IT 201',
            'room' => 'Room 201',
            'instructor' => 'Prof. Santos',
        ],
        [
            'id' => 2,
            'day' => 'Tuesday',
            'time' => '10:00 AM - 12:00 PM',
            'subject' => 'Database Management',
            'code' => 'IT 202',
            'room' => 'Room 202',
            'instructor' => 'Prof. Reyes',
        ],
        [
            'id' => 3,
            'day' => 'Wednesday',
            'time' => '1:00 PM - 3:00 PM',
            'subject' => 'Mobile Application Development',
            'code' => 'IT 203',
            'room' => 'Laboratory 1',
            'instructor' => 'Prof. Cruz',
        ],
        [
            'id' => 4,
            'day' => 'Thursday',
            'time' => '8:00 AM - 10:00 AM',
            'subject' => 'Computer Networking',
            'code' => 'IT 204',
            'room' => 'Room 203',
            'instructor' => 'Prof. Garcia',
        ],
        [
            'id' => 5,
            'day' => 'Friday',
            'time' => '10:00 AM - 12:00 PM',
            'subject' => 'Systems Analysis and Design',
            'code' => 'IT 205',
            'room' => 'Room 204',
            'instructor' => 'Prof. Mendoza',
        ],
    ]);

});


/*
|--------------------------------------------------------------------------
| GRADES (static data, per student)
|--------------------------------------------------------------------------
|   GET /api/students/{id}/grades  -> grades of that student only
|   GET /api/grades                -> all grades (optional ?student_id=1)
|--------------------------------------------------------------------------
*/

$subjects = [
    ['code' => 'IT 201', 'name' => 'Web Development',                'units' => 3],
    ['code' => 'IT 202', 'name' => 'Database Management',            'units' => 3],
    ['code' => 'IT 203', 'name' => 'Mobile Application Development', 'units' => 3],
    ['code' => 'IT 204', 'name' => 'Computer Networking',            'units' => 3],
    ['code' => 'IT 205', 'name' => 'Systems Analysis and Design',    'units' => 3],
];

// student id => grades, in the same order as $subjects above
$gradeTable = [
    1  => [1.25, 1.50, 1.25, 1.75, 1.50], // Juan Dela Cruz
    2  => [1.50, 1.25, 1.75, 1.50, 2.00], // Maria Santos
    3  => [1.75, 2.00, 1.50, 1.75, 1.50], // Pedro Reyes
    4  => [1.25, 1.25, 1.50, 1.50, 1.75], // Ana Cruz
    5  => [2.00, 1.75, 2.25, 2.00, 1.75], // Jose Garcia
    6  => [1.50, 1.50, 1.25, 1.75, 1.25], // Rosa Mendoza
    7  => [2.25, 2.00, 2.00, 1.75, 2.25], // Carlos Torres
    8  => [1.25, 1.50, 1.50, 1.25, 1.50], // Elena Flores
    9  => [1.75, 1.75, 2.00, 2.25, 2.00], // Mark Villanueva
    10 => [1.50, 1.25, 1.25, 1.50, 1.25], // Sofia Ramirez
];

$grades = [];

foreach ($students as $student) {
    foreach ($subjects as $index => $subject) {
        $grade = $gradeTable[$student['id']][$index];

        $grades[] = [
            'id'             => $student['id'] * 100 + $index + 1,
            'student_id'     => $student['id'],
            'student_number' => $student['student_number'],
            'student_name'   => $student['name'],
            'course_code'    => $subject['code'],
            'course_name'    => $subject['name'],
            'units'          => $subject['units'],
            'grade'          => $grade,
            'remarks'        => $grade <= 3.00 ? 'Passed' : 'Failed',
        ];
    }
}

// Grades of ONE student, by id
Route::get('/students/{id}/grades', function ($id) use ($students, $grades) {

    $student = collect($students)->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    $own = array_values(array_filter(
        $grades,
        fn ($g) => $g['student_id'] === (int) $id
    ));

    return response()->json($own);
});

// All grades (can be filtered with ?student_id=1)
Route::get('/grades', function () use ($grades) {

    $studentId = request('student_id');

    if ($studentId !== null) {
        $grades = array_values(array_filter(
            $grades,
            fn ($g) => $g['student_id'] === (int) $studentId
        ));
    }

    return response()->json($grades);
});


/*
|--------------------------------------------------------------------------
| SCHOOL
|--------------------------------------------------------------------------
*/

Route::get('/school', function () use ($students) {

    // NOTE: sample information. Replace with the real details of your school.
    return response()->json([
        'name' => 'Opol Community College',
        'short_name' => 'OCC',
        'motto' => 'Education for Community Growth',
        'established' => '1996',
        'president' => 'Office of the College President',

        'address' => 'Opol, Misamis Oriental, Philippines',
        'email' => 'info@occ.edu.ph',
        'phone' => '09123456789',
        'website' => 'www.occ.edu.ph',
        'office_hours' => 'Monday - Friday, 8:00 AM - 5:00 PM',

        'academic_year' => '2026 - 2027',
        'semester' => '1st Semester',

        'mission' => 'To provide accessible, quality education that develops skilled, responsible and service-oriented graduates who contribute to the growth of the community.',
        'vision' => 'A leading community college recognized for excellence in instruction, research and service.',

        'core_values' => ['Excellence', 'Integrity', 'Service', 'Community', 'Respect'],

        'programs' => [
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Business Administration',
            'Bachelor of Science in Hospitality Management',
            'Bachelor of Elementary Education',
        ],

        'stats' => [
            'students' => count($students),
            'programs' => 4,
            'faculty' => 25,
        ],
    ]);

});


/*
|--------------------------------------------------------------------------
| ANNOUNCEMENTS
|--------------------------------------------------------------------------
*/

Route::get('/announcements', function () {

    return response()->json([
        [
            'id' => 1,
            'title' => 'Enrollment Period',
            'message' => 'Enrollment for the new academic year is now open.',
            'date' => 'October 1, 2026',
        ],
        [
            'id' => 2,
            'title' => 'Midterm Examination',
            'message' => 'Midterm examinations will be held soon.',
            'date' => 'October 15, 2026',
        ],
        [
            'id' => 3,
            'title' => 'School Activity',
            'message' => 'Students are invited to participate in the upcoming school activity.',
            'date' => 'October 20, 2026',
        ],
    ]);

});