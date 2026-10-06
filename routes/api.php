<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$students = [
    [
        'id' => 1,
        'student_number' => '2026-0001',
        'name' => 'Juan Dela Cruz',
        'course' => 'Bachelor of Science in Information Technology',
        'year_level' => 3,
        'section' => 'BSIT-3A',
        'email' => 'juan.delacruz@example.com',
        'phone' => '09123456789',
        'status' => 'Active',
        'gender' => 'Male',
        'birth_date' => '2004-05-15',
        'address' => 'Cagayan de Oro City',
        'guardian' => 'Maria Dela Cruz',
        'guardian_phone' => '09187654321',
    ],
    [
        'id' => 2,
        'student_number' => '2026-0002',
        'name' => 'Maria Santos',
        'course' => 'Bachelor of Science in Information Technology',
        'year_level' => 2,
        'section' => 'BSIT-2A',
        'email' => 'maria.santos@example.com',
        'phone' => '09123456780',
        'status' => 'Active',
        'gender' => 'Female',
        'birth_date' => '2004-08-21',
        'address' => 'Opol, Misamis Oriental',
        'guardian' => 'Rosario Santos',
        'guardian_phone' => '09187654322',
    ],
    [
        'id' => 3,
        'student_number' => '2026-0003',
        'name' => 'Pedro Reyes',
        'course' => 'Bachelor of Science in Business Administration',
        'year_level' => 2,
        'section' => 'BSBA-2A',
        'email' => 'pedro.reyes@example.com',
        'phone' => '09123456781',
        'status' => 'Active',
        'gender' => 'Male',
        'birth_date' => '2003-11-02',
        'address' => 'Cagayan de Oro City',
        'guardian' => 'Antonio Reyes',
        'guardian_phone' => '09187654323',
    ],
    [
        'id' => 4,
        'student_number' => '2026-0004',
        'name' => 'Ana Cruz',
        'course' => 'Bachelor of Science in Business Administration',
        'year_level' => 2,
        'section' => 'BSBA-2B',
        'email' => 'ana.cruz@example.com',
        'phone' => '09123456782',
        'status' => 'Active',
        'gender' => 'Female',
        'birth_date' => '2007-03-12',
        'address' => 'Opol, Misamis Oriental',
        'guardian' => 'Luz Cruz',
        'guardian_phone' => '09187654324',
    ],
    [
        'id' => 5,
        'student_number' => '2026-0005',
        'name' => 'Pepper Alonzo',
        'course' => 'Bachelor of Elementary Education',
        'year_level' => 2,
        'section' => 'BEED-2B',
        'email' => 'pepper.alonzo@example.com',
        'phone' => '09122144574',
        'status' => 'Active',
        'gender' => 'Female',
        'birth_date' => '2004-02-14',
        'address' => 'Opol, Misamis Oriental',
        'guardian' => 'Annabelle Alonzo',
        'guardian_phone' => '09187654324',
    ],
    [
        'id' => 6,
        'student_number' => '2026-0006',
        'name' => 'Luiz Munchala',
        'course' => 'Bachelor of Elementary Education',
        'year_level' => 2,
        'section' => 'BEED-2B',
        'email' => 'luiz.munchala@example.com',
        'phone' => '09358882701',
        'status' => 'Active',
        'gender' => 'Male',
        'birth_date' => '2004-06-10',
        'address' => 'Opol, Misamis Oriental',
        'guardian' => 'Annabelle Munchala',
        'guardian_phone' => '09506667253',
    ],
];

// Only these fields can be changed through PUT /students/{id}
// (id, student_number, section, status, etc. are never overwritten)
$editableFields = [
    'name', 'email', 'course', 'year_level',
    'phone', 'address', 'guardian', 'guardian_phone',
];

// Saved profile edits (see PUT /students/{id})
$overridesPath = storage_path('app/student_overrides.json');

$overrides = [];

if (is_file($overridesPath)) {
    $decoded = json_decode((string) file_get_contents($overridesPath), true);
    // A corrupted or unexpected file is ignored instead of crashing every route
    $overrides = is_array($decoded) ? $decoded : [];
}

$students = array_map(function ($s) use ($overrides, $editableFields) {
    $saved = $overrides[$s['id']] ?? [];
    $saved = is_array($saved)
        ? array_intersect_key($saved, array_flip($editableFields))
        : [];

    return array_merge($s, $saved);
}, $students);

// "BSIT-2A" -> "BSIT" (safe even if the section is missing)
$programOf = fn ($student) => strtoupper(explode('-', (string) ($student['section'] ?? ''))[0]);




$subjectsByProgram = [
    'BSIT' => [
        ['code' => 'IT 201', 'name' => 'Web Development',                'units' => 3],
        ['code' => 'IT 202', 'name' => 'Database Management',            'units' => 3],
        ['code' => 'IT 203', 'name' => 'Mobile Application Development', 'units' => 3],
        ['code' => 'IT 204', 'name' => 'Computer Networking',            'units' => 3],
        ['code' => 'IT 205', 'name' => 'Systems Analysis and Design',    'units' => 3],
    ],
    'BSBA' => [
        ['code' => 'BA 101', 'name' => 'Principles of Management',  'units' => 3],
        ['code' => 'BA 102', 'name' => 'Financial Accounting',      'units' => 3],
        ['code' => 'BA 103', 'name' => 'Marketing Management',      'units' => 3],
        ['code' => 'BA 104', 'name' => 'Business Law and Ethics',   'units' => 3],
        ['code' => 'BA 105', 'name' => 'Human Resource Management', 'units' => 3],
    ],
    'BEED' => [
        ['code' => 'ED 101', 'name' => 'Child and Adolescent Development', 'units' => 3],
        ['code' => 'ED 102', 'name' => 'Teaching Strategies',              'units' => 3],
        ['code' => 'ED 103', 'name' => 'Assessment of Learning',           'units' => 3],
        ['code' => 'ED 104', 'name' => 'Principles of Teaching',           'units' => 3],
        ['code' => 'ED 105', 'name' => 'Curriculum Development',           'units' => 3],
    ],
];

// student id => grades, in the same order as that student's program subjects above
$gradeTable = [
    1 => [1.25, 1.50, 1.25, 1.75, 1.50], // Juan Dela Cruz   (BSIT)
    2 => [1.50, 1.25, 1.75, 1.50, 2.00], // Maria Santos     (BSIT)
    3 => [1.75, 2.00, 1.50, 1.75, 1.50], // Pedro Reyes      (BSBA)
    4 => [1.25, 1.25, 1.50, 1.50, 1.75], // Ana Cruz         (BSBA)
    5 => [2.00, 1.75, 2.25, 2.00, 1.75], // Pepper Alonzo    (BEED)
    6 => [1.50, 1.50, 1.25, 1.75, 1.25], // Luiz Munchala    (BEED)
];

$grades = [];

foreach ($students as $student) {
    $program  = $programOf($student);
    $subjects = $subjectsByProgram[$program] ?? [];

    foreach ($subjects as $index => $subject) {
        $grade = $gradeTable[$student['id']][$index] ?? null;

        if ($grade === null) {
            continue; // no grade entered for this subject yet
        }

        $grades[] = [
            'id'             => $student['id'] * 100 + $index + 1,
            'student_id'     => $student['id'],
            'student_number' => $student['student_number'],
            'student_name'   => $student['name'],
            'program'        => $program,
            'course_code'    => $subject['code'],
            'course_name'    => $subject['name'],
            'units'          => $subject['units'],
            'grade'          => $grade,
            'remarks'        => $grade <= 3.00 ? 'Passed' : 'Failed',
        ];
    }
}


Route::get('/students', function () use ($students) {
    return response()->json($students);
});


// Search by name, student number or email
Route::get('/students/search', function (Request $request) use ($students) {

    $q = strtolower(trim((string) $request->query('q', '')));

    if ($q === '') {
        return response()->json(['message' => 'Provide a search term, e.g. ?q=juan'], 422);
    }

    $found = array_values(array_filter($students, fn ($s) =>
        str_contains(strtolower($s['name']), $q) ||
        str_contains(strtolower($s['student_number']), $q) ||
        str_contains(strtolower($s['email']), $q)
    ));

    return response()->json($found);
});

// Students in one section, e.g. /students/section/BSIT-2A
Route::get('/students/section/{section}', function ($section) use ($students) {

    $list = array_values(array_filter(
        $students,
        fn ($s) => strcasecmp($s['section'], $section) === 0
    ));

    return response()->json($list);
});

// Students with one status, e.g. /students/status/Active
Route::get('/students/status/{status}', function ($status) use ($students) {

    $list = array_values(array_filter(
        $students,
        fn ($s) => strcasecmp($s['status'], $status) === 0
    ));

    return response()->json($list);
});

// Totals and breakdowns
Route::get('/students/summary', function () use ($students) {

    $all = collect($students);

    return response()->json([
        'total'         => $all->count(),
        'by_status'     => $all->countBy('status'),
        'by_course'     => $all->countBy('course'),
        'by_year_level' => $all->countBy('year_level'),
        'by_gender'     => $all->countBy('gender'),
    ]);
});


Route::get('/students/{id}', function ($id) use ($students) {

    $student = collect($students)->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    return response()->json($student);
})->whereNumber('id');


// Builds a response with only the chosen fields of one student
$studentInfo = function ($id, array $fields) use ($students) {

    $student = collect($students)->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    return response()->json(
        ['id' => $student['id']] + array_intersect_key($student, array_flip($fields))
    );
};

// Personal information
Route::get('/students/{id}/personal', function ($id) use ($studentInfo) {
    return $studentInfo($id, [
        'student_number', 'name', 'gender', 'birth_date', 'address',
    ]);
})->whereNumber('id');

// Contact information
Route::get('/students/{id}/contact', function ($id) use ($studentInfo) {
    return $studentInfo($id, [
        'student_number', 'name', 'email', 'phone', 'address',
    ]);
})->whereNumber('id');

// Academic information
Route::get('/students/{id}/academic', function ($id) use ($studentInfo) {
    return $studentInfo($id, [
        'student_number', 'name', 'course', 'year_level', 'section', 'status',
    ]);
})->whereNumber('id');

// Guardian information
Route::get('/students/{id}/guardian', function ($id) use ($studentInfo) {
    return $studentInfo($id, [
        'student_number', 'name', 'guardian', 'guardian_phone',
    ]);
})->whereNumber('id');

// Grades of ONE student, by id (subjects of that student's program)
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
})->whereNumber('id');

// All grades (can be filtered with ?student_id=1)
Route::get('/grades', function (Request $request) use ($grades) {

    $studentId = $request->query('student_id');

    if ($studentId !== null && $studentId !== '') {
        $grades = array_values(array_filter(
            $grades,
            fn ($g) => $g['student_id'] === (int) $studentId
        ));
    }

    return response()->json($grades);
});

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
    $saved = [];

    if (is_file($overridesPath)) {
        $decoded = json_decode((string) file_get_contents($overridesPath), true);
        $saved = is_array($decoded) ? $decoded : [];
    }

    $saved[$student['id']] = array_merge(
        is_array($saved[$student['id']] ?? null) ? $saved[$student['id']] : [],
        $data
    );

    // Make sure the storage folder exists before writing
    $dir = dirname($overridesPath);

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $written = @file_put_contents(
        $overridesPath,
        json_encode($saved, JSON_PRETTY_PRINT),
        LOCK_EX
    );

    if ($written === false) {
        return response()->json([
            'message' => 'Could not save changes. Check that storage/app is writable.',
        ], 500);
    }

    return response()->json(array_merge($student, $data));
})->whereNumber('id');


Route::get('/courses', function () {

    // NOTE: sample descriptions. Replace with your real course outlines.
    $courses = [
        [
            'id' => 1,
            'code' => 'IT 201',
            'name' => 'Web Development',
            'units' => 3,
            'prerequisite' => 'None',
            'description' => 'Introduces the fundamentals of building websites. Students learn HTML, CSS and JavaScript, responsive layouts, and how to publish a simple, working web page.',
        ],
        [
            'id' => 2,
            'code' => 'IT 202',
            'name' => 'Database Management',
            'units' => 3,
            'prerequisite' => 'None',
            'description' => 'Covers database design and management. Topics include the relational model, entity-relationship diagrams, normalization, SQL queries, and basic database administration.',
        ],
        [
            'id' => 3,
            'code' => 'IT 203',
            'name' => 'Mobile Application Development',
            'units' => 3,
            'prerequisite' => 'IT 201',
            'description' => 'Teaches students to design and build mobile apps. Covers screen layouts, navigation, working with APIs, and testing apps on real devices.',
        ],
        [
            'id' => 4,
            'code' => 'IT 204',
            'name' => 'Computer Networking',
            'units' => 3,
            'prerequisite' => 'None',
            'description' => 'Explains how computer networks work. Includes the OSI and TCP/IP models, IP addressing, routing and switching, and basic network setup and troubleshooting.',
        ],
        [
            'id' => 5,
            'code' => 'IT 205',
            'name' => 'Systems Analysis and Design',
            'units' => 3,
            'prerequisite' => 'IT 202',
            'description' => 'Focuses on planning and designing information systems. Students gather requirements, model processes and data, and prepare system documentation.',
        ],
        [
            'id' => 6,
            'code' => 'IT 206',
            'name' => 'Object-Oriented Programming',
            'units' => 3,
            'prerequisite' => 'None',
            'description' => 'Introduces object-oriented concepts such as classes, objects, inheritance, polymorphism and encapsulation, applied through hands-on programming exercises.',
        ],
        [
            'id' => 7,
            'code' => 'IT 207',
            'name' => 'Data Structures and Algorithms',
            'units' => 3,
            'prerequisite' => 'IT 206',
            'description' => 'Studies how data is organized and processed efficiently. Covers arrays, linked lists, stacks, queues, trees, sorting and searching algorithms.',
        ],
        [
            'id' => 8,
            'code' => 'IT 208',
            'name' => 'Human-Computer Interaction',
            'units' => 3,
            'prerequisite' => 'IT 201',
            'description' => 'Looks at how people interact with software. Students learn usability principles, user research, wireframing and prototyping of user-friendly interfaces.',
        ],
        [
            'id' => 9,
            'code' => 'IT 209',
            'name' => 'Information Assurance and Security',
            'units' => 3,
            'prerequisite' => 'IT 204',
            'description' => 'Covers the basics of protecting information systems. Topics include common threats, authentication, encryption, secure coding practices and security policies.',
        ],
        [
            'id' => 10,
            'code' => 'IT 210',
            'name' => 'Integrative Programming and Technologies',
            'units' => 3,
            'prerequisite' => 'IT 203',
            'description' => 'Teaches how to combine different systems and technologies. Students build applications that connect a mobile or web front end to a back-end API and database.',
        ],

        [
            'id' => 11,
            'code' => 'BA 101',
            'name' => 'Principles of Management',
            'units' => 3,
            'prerequisite' => 'None',
            'program_code' => 'BSBA',
            'description' => 'Introduces the core functions of management: planning, organizing, leading and controlling. Students study how organizations are structured and how managers make decisions.',
        ],
        [
            'id' => 12,
            'code' => 'BA 102',
            'name' => 'Financial Accounting',
            'units' => 3,
            'prerequisite' => 'None',
            'program_code' => 'BSBA',
            'description' => 'Covers the accounting cycle and the preparation of financial statements. Students learn to record, classify, analyze and report business transactions.',
        ],
        [
            'id' => 13,
            'code' => 'BA 103',
            'name' => 'Marketing Management',
            'units' => 3,
            'prerequisite' => 'BA 101',
            'program_code' => 'BSBA',
            'description' => 'Looks at how businesses understand customers and promote products. Topics include market research, consumer behavior, pricing, promotion and marketing strategy.',
        ],
        [
            'id' => 14,
            'code' => 'BA 104',
            'name' => 'Business Law and Ethics',
            'units' => 3,
            'prerequisite' => 'None',
            'program_code' => 'BSBA',
            'description' => 'Explains the legal rules that affect business, such as contracts, partnerships and corporations, along with ethical standards and social responsibility in business.',
        ],
        [
            'id' => 15,
            'code' => 'BA 105',
            'name' => 'Human Resource Management',
            'units' => 3,
            'prerequisite' => 'BA 101',
            'program_code' => 'BSBA',
            'description' => 'Focuses on managing people in organizations. Covers recruitment, training, performance evaluation, compensation and employee relations.',
        ],

        [
            'id' => 16,
            'code' => 'ED 101',
            'name' => 'Child and Adolescent Development',
            'units' => 3,
            'prerequisite' => 'None',
            'program_code' => 'BEED',
            'description' => 'Studies the physical, emotional, social and cognitive growth of children and adolescents, and how development affects learning in the classroom.',
        ],
        [
            'id' => 17,
            'code' => 'ED 102',
            'name' => 'Teaching Strategies',
            'units' => 3,
            'prerequisite' => 'ED 101',
            'program_code' => 'BEED',
            'description' => 'Teaches methods and techniques for effective classroom instruction, including lesson planning, classroom management and learner engagement.',
        ],
        [
            'id' => 18,
            'code' => 'ED 103',
            'name' => 'Assessment of Learning',
            'units' => 3,
            'prerequisite' => 'ED 102',
            'program_code' => 'BEED',
            'description' => 'Covers how to design, use and interpret tests and other assessment tools to measure and improve student learning.',
        ],
        [
            'id' => 19,
            'code' => 'ED 104',
            'name' => 'Principles of Teaching',
            'units' => 3,
            'prerequisite' => 'None',
            'program_code' => 'BEED',
            'description' => 'Introduces the foundations of the teaching profession, including learning theories, the role of the teacher and professional ethics.',
        ],
        [
            'id' => 20,
            'code' => 'ED 105',
            'name' => 'Curriculum Development',
            'units' => 3,
            'prerequisite' => 'ED 102',
            'program_code' => 'BEED',
            'description' => 'Explains how school curricula are planned, organized and evaluated, and how teachers adapt lessons to learner needs.',
        ],
    ];

    // Courses without a program_code (the IT subjects) default to BSIT.
    // The "+" operator keeps any program_code already set above.
    return response()->json(
        array_map(fn ($c) => $c + ['program_code' => 'BSIT'], $courses)
    );

});



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
            'code' => 'BEED',
            'name' => 'Bachelor of Elementary Education',
            'department' => 'Education',
            'duration' => '4 Years',
        ],
    ]);

});



$MWF  = ['Monday', 'Wednesday', 'Friday'];
$TTH  = ['Tuesday', 'Thursday'];
$TTHS = ['Tuesday', 'Thursday', 'Saturday'];

// program => ['A' => [...], 'B' => [...]]
// every row: [code, subject, days, time, room, instructor]
$scheduleTable = [

  
    'BSIT' => [
        'A' => [
            ['IT 201', 'Web Development',                $MWF,  '8:00 AM - 9:00 AM',   'Room 201',     'Prof. Santos'],
            ['IT 202', 'Database Management',            $TTH,  '8:00 AM - 9:30 AM',   'Room 202',     'Prof. Reyes'],
            ['IT 203', 'Mobile Application Development', $MWF,  '10:00 AM - 11:00 AM', 'Laboratory 1', 'Prof. Cruz'],
            ['IT 204', 'Computer Networking',            $TTH,  '10:00 AM - 11:30 AM', 'Room 203',     'Prof. Garcia'],
            ['IT 205', 'Systems Analysis and Design',    $TTHS, '1:00 PM - 2:00 PM',   'Room 204',     'Prof. Mendoza'],
        ],
        'B' => [
            ['IT 201', 'Web Development',                $TTH,  '1:00 PM - 2:30 PM',   'Room 201',     'Prof. Santos'],
            ['IT 202', 'Database Management',            $MWF,  '1:00 PM - 2:00 PM',   'Room 202',     'Prof. Reyes'],
            ['IT 203', 'Mobile Application Development', $TTH,  '3:00 PM - 4:30 PM',   'Laboratory 1', 'Prof. Cruz'],
            ['IT 204', 'Computer Networking',            $MWF,  '2:00 PM - 3:00 PM',   'Room 203',     'Prof. Garcia'],
            ['IT 205', 'Systems Analysis and Design',    $MWF,  '3:00 PM - 4:00 PM',   'Room 204',     'Prof. Mendoza'],
        ],
    ],


    'BSBA' => [
        'A' => [
            ['BA 101', 'Principles of Management',  $MWF,  '9:00 AM - 10:00 AM',  'Room 301', 'Prof. Villanueva'],
            ['BA 102', 'Financial Accounting',      $TTH,  '9:00 AM - 10:30 AM',  'Room 302', 'Prof. Ramos'],
            ['BA 103', 'Marketing Management',      $MWF,  '11:00 AM - 12:00 PM', 'Room 303', 'Prof. Bautista'],
            ['BA 104', 'Business Law and Ethics',   $TTH,  '1:00 PM - 2:30 PM',   'Room 304', 'Prof. Aquino'],
            ['BA 105', 'Human Resource Management', $TTHS, '3:00 PM - 4:00 PM',   'Room 305', 'Prof. Lopez'],
        ],
        'B' => [
            ['BA 101', 'Principles of Management',  $TTH,  '3:00 PM - 4:30 PM',   'Room 301', 'Prof. Villanueva'],
            ['BA 102', 'Financial Accounting',      $MWF,  '1:00 PM - 2:00 PM',   'Room 302', 'Prof. Ramos'],
            ['BA 103', 'Marketing Management',      $TTH,  '1:00 PM - 2:30 PM',   'Room 303', 'Prof. Bautista'],
            ['BA 104', 'Business Law and Ethics',   $MWF,  '8:00 AM - 9:00 AM',   'Room 304', 'Prof. Aquino'],
            ['BA 105', 'Human Resource Management', $MWF,  '2:00 PM - 3:00 PM',   'Room 305', 'Prof. Lopez'],
        ],
    ],

   
    'BEED' => [
        'A' => [
            ['ED 101', 'Child and Adolescent Development', $MWF,  '10:00 AM - 11:00 AM', 'Room 401', 'Prof. Dizon'],
            ['ED 102', 'Teaching Strategies',              $TTH,  '8:00 AM - 9:30 AM',   'Room 402', 'Prof. Navarro'],
            ['ED 103', 'Assessment of Learning',           $MWF,  '1:00 PM - 2:00 PM',   'Room 403', 'Prof. Torres'],
            ['ED 104', 'Principles of Teaching',           $TTH,  '1:00 PM - 2:30 PM',   'Room 404', 'Prof. Flores'],
            ['ED 105', 'Curriculum Development',           $TTHS, '10:00 AM - 11:00 AM', 'Room 405', 'Prof. Castillo'],
        ],
        'B' => [
            ['ED 101', 'Child and Adolescent Development', $TTH,  '3:00 PM - 4:30 PM',   'Room 401', 'Prof. Dizon'],
            ['ED 102', 'Teaching Strategies',              $MWF,  '8:00 AM - 9:00 AM',   'Room 402', 'Prof. Navarro'],
            ['ED 103', 'Assessment of Learning',           $TTH,  '10:00 AM - 11:30 AM', 'Room 403', 'Prof. Torres'],
            ['ED 104', 'Principles of Teaching',           $MWF,  '2:00 PM - 3:00 PM',   'Room 404', 'Prof. Flores'],
            ['ED 105', 'Curriculum Development',           $MWF,  '3:00 PM - 4:00 PM',   'Room 405', 'Prof. Castillo'],
        ],
    ],
];

// Which timetable each student gets: the 1st student in a program -> A,

$trackOf  = [];
$position = [];

foreach ($students as $s) {
    $program = $programOf($s);
    $n       = $position[$program] ?? 0;

    $position[$program] = $n + 1;
    $trackOf[$s['id']]  = $n % 2 === 0 ? 'A' : 'B';
}

$schedules = [];

foreach ($students as $student) {
    $program = $programOf($student);
    $track   = $trackOf[$student['id']];
    $classes = $scheduleTable[$program][$track] ?? [];

    foreach ($classes as $index => [$code, $subject, $days, $time, $room, $instructor]) {
        $schedules[] = [
            'id'            => $student['id'] * 100 + $index + 1,
            'student_id'    => $student['id'],
            'program_code'  => $program,
            'schedule_type' => $track,
            'code'          => $code,
            'subject'       => $subject,
            'days'          => $days,
            'day'           => implode(' / ', array_map(fn ($d) => substr($d, 0, 3), $days)),
            'time'          => $time,
            'room'          => $room,
            'instructor'    => $instructor,
        ];
    }
}

// All schedules (optional filters: ?program=BSIT and/or ?student_id=1)
Route::get('/schedules', function (Request $request) use ($schedules) {

    $program   = strtoupper(trim((string) $request->query('program', '')));
    $studentId = $request->query('student_id');

    if ($program !== '') {
        $schedules = array_values(array_filter(
            $schedules,
            fn ($s) => $s['program_code'] === $program
        ));
    }

    if ($studentId !== null && $studentId !== '') {
        $schedules = array_values(array_filter(
            $schedules,
            fn ($s) => $s['student_id'] === (int) $studentId
        ));
    }

    return response()->json($schedules);
});

// Schedule of ONE student (the timetable assigned to that student)
Route::get('/students/{id}/schedules', function ($id) use ($students, $schedules) {

    $student = collect($students)->firstWhere('id', (int) $id);

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    $own = array_values(array_filter(
        $schedules,
        fn ($s) => $s['student_id'] === (int) $id
    ));

    return response()->json($own);
})->whereNumber('id');

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
            'Bachelor of Elementary Education',
        ],

        'stats' => [
            'students' => count($students),
            'programs' => 3,
            'faculty' => 25,
        ],
    ]);

});



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


Route::fallback(function (Request $request) {
    return response()->json([
        'message' => 'Endpoint not found: /' . $request->path(),
    ], 404);
});

$admins = [
    [
        'id' => 1,
        'admin_number' => 'ADM-0001',
        'name' => 'Admin',
        'role' => 'Administrator',
        'office' => 'Office of the College President',
        'email' => 'admin@occ.edu.ph',
        'phone' => '09123456789',
        'status' => 'Active',
        'access' => [
            'students', 'courses', 'programs', 'schedules',
            'grades', 'announcements', 'school',
        ],
    ],
];

// Only these fields can be changed through PUT /admins/{id}
$adminEditable = ['name', 'email', 'phone', 'office'];

// Saved admin edits
$adminOverridesPath = storage_path('app/admin_overrides.json');

$adminOverrides = [];

if (is_file($adminOverridesPath)) {
    $decoded = json_decode((string) file_get_contents($adminOverridesPath), true);
    $adminOverrides = is_array($decoded) ? $decoded : [];
}

$admins = array_map(function ($a) use ($adminOverrides, $adminEditable) {
    $saved = $adminOverrides[$a['id']] ?? [];
    $saved = is_array($saved)
        ? array_intersect_key($saved, array_flip($adminEditable))
        : [];

    return array_merge($a, $saved);
}, $admins);

// The admin who is using the app (first admin record)
Route::get('/admin', function () use ($admins) {
    return response()->json($admins[0]);
});

// All admins
Route::get('/admins', function () use ($admins) {
    return response()->json($admins);
});

// One admin
Route::get('/admins/{id}', function ($id) use ($admins) {

    $admin = collect($admins)->firstWhere('id', (int) $id);

    if (!$admin) {
        return response()->json(['message' => 'Admin not found'], 404);
    }

    return response()->json($admin);
})->whereNumber('id');

// Update the admin profile
Route::put('/admins/{id}', function (Request $request, $id) use ($admins, $adminOverridesPath) {

    $admin = collect($admins)->firstWhere('id', (int) $id);

    if (!$admin) {
        return response()->json(['message' => 'Admin not found'], 404);
    }

    // admin_number, role, status and access are intentionally not editable
    $data = $request->validate([
        'name'   => 'sometimes|required|string|max:100',
        'email'  => 'sometimes|required|email|max:100',
        'office' => 'sometimes|required|string|max:100',
        'phone'  => 'sometimes|nullable|string|max:20',
    ]);

    // Load current saved edits, merge the new ones, write back
    $saved = [];

    if (is_file($adminOverridesPath)) {
        $decoded = json_decode((string) file_get_contents($adminOverridesPath), true);
        $saved = is_array($decoded) ? $decoded : [];
    }

    $saved[$admin['id']] = array_merge(
        is_array($saved[$admin['id']] ?? null) ? $saved[$admin['id']] : [],
        $data
    );

    $dir = dirname($adminOverridesPath);

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $written = @file_put_contents(
        $adminOverridesPath,
        json_encode($saved, JSON_PRETTY_PRINT),
        LOCK_EX
    );

    if ($written === false) {
        return response()->json([
            'message' => 'Could not save changes. Check that storage/app is writable.',
        ], 500);
    }

    return response()->json(array_merge($admin, $data));
})->whereNumber('id');