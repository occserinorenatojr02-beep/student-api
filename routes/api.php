<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


$notFound = fn (string $what) => response()->json(['message' => "$what not found"], 404);
$find     = fn (array $rows, $id) => collect($rows)->firstWhere('id', (int) $id);

// Read a JSON file; a missing or corrupted file just gives []
$readJson = function (string $path): array {
    $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
    return is_array($data) ? $data : [];
};

// Merge saved edits into rows (only the allowed fields)
$applyEdits = fn (array $rows, array $saved, array $fields) => array_map(
    fn ($r) => array_merge($r, array_intersect_key(
        is_array($saved[$r['id']] ?? null) ? $saved[$r['id']] : [],
        array_flip($fields)
    )),
    $rows
);

// PUT/Prefix[id]
$registerUpdate = function (string $prefix, string $label, array $rows, string $path, array $rules) use ($readJson, $find, $notFound) {
    Route::put("/$prefix/{id}", function (Request $request, $id) use ($rows, $path, $rules, $label, $readJson, $find, $notFound) {

        $row = $find($rows, $id);
        if (!$row) {
            return $notFound($label);
        }

        $data  = $request->validate($rules);
        $saved = $readJson($path);

        $saved[$row['id']] = array_merge(
            is_array($saved[$row['id']] ?? null) ? $saved[$row['id']] : [],
            $data
        );

        @mkdir(dirname($path), 0755, true);

        if (@file_put_contents($path, json_encode($saved, JSON_PRETTY_PRINT), LOCK_EX) === false) {
            return response()->json(['message' => 'Could not save changes. Check that storage/app is writable.'], 500);
        }

        return response()->json(array_merge($row, $data));
    })->whereNumber('id');
};

//Data
$programs = [
    ['id' => 1, 'code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology',  'department' => 'Information Technology',  'duration' => '4 Years'],
    ['id' => 2, 'code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration', 'department' => 'Business Administration', 'duration' => '4 Years'],
    ['id' => 3, 'code' => 'BEED', 'name' => 'Bachelor of Elementary Education',               'department' => 'Education',               'duration' => '4 Years'],
];
$programName = collect($programs)->pluck('name', 'code')->all();

// "BSIT-2A" -> "BSIT"
$programOf = fn ($student) => strtoupper(explode('-', (string) ($student['section'] ?? ''))[0]);

/* ---------- Students ----------*/
$studentRows = [
    ['Juan Dela Cruz', 'juan.delacruz@example.com', 'BSIT-3A', 3, '09123456789', 'Male',   '2004-05-15', 'Cagayan de Oro City',   'Maria Dela Cruz',  '09187654321'],
    ['Maria Santos',   'maria.santos@example.com',   'BSIT-2A', 2, '09123456780', 'Female', '2004-08-21', 'Opol, Misamis Oriental', 'Rosario Santos',    '09187654322'],
    ['Pedro Reyes',    'pedro.reyes@example.com',    'BSBA-2A', 2, '09123456781', 'Male',   '2003-11-02', 'Cagayan de Oro City',   'Antonio Reyes',     '09187654323'],
    ['Ana Cruz',       'ana.cruz@example.com',       'BSBA-2B', 2, '09123456782', 'Female', '2007-03-12', 'Opol, Misamis Oriental', 'Luz Cruz',          '09187654324'],
    ['Pepper Alonzo',  'pepper.alonzo@example.com',  'BEED-2B', 2, '09122144574', 'Female', '2004-02-14', 'Opol, Misamis Oriental', 'Annabelle Alonzo',  '09187654324'],
    ['Luiz Munchala',  'luiz.munchala@example.com',  'BEED-2B', 2, '09358882701', 'Male',   '2004-06-10', 'Opol, Misamis Oriental', 'Annabelle Munchala', '09506667253'],
];

$studentKeys = ['name', 'email', 'section', 'year_level', 'phone', 'gender', 'birth_date', 'address', 'guardian', 'guardian_phone'];

$students = [];
foreach ($studentRows as $i => $row) {
    $s = array_combine($studentKeys, $row);

    $students[] = ['id' => $i + 1, 'student_number' => sprintf('2026-%04d', $i + 1)]
        + $s
        + ['course' => $programName[$programOf($s)] ?? '', 'status' => 'Active'];
}

// Apply saved profile edits (PUT /students/{id})
$studentEditable = ['name', 'email', 'course', 'year_level', 'phone', 'address', 'guardian', 'guardian_phone'];
$studentsPath    = storage_path('app/student_overrides.json');
$students        = $applyEdits($students, $readJson($studentsPath), $studentEditable);

/* ---------- Courses ----------*/
$courseTable = [
    'BSIT' => [
        ['IT 201', 'Web Development',                        'None',   'Introduces the fundamentals of building websites. Students learn HTML, CSS and JavaScript, responsive layouts, and how to publish a simple, working web page.'],
        ['IT 202', 'Database Management',                    'None',   'Covers database design and management. Topics include the relational model, entity-relationship diagrams, normalization, SQL queries, and basic database administration.'],
        ['IT 203', 'Mobile Application Development',         'IT 201', 'Teaches students to design and build mobile apps. Covers screen layouts, navigation, working with APIs, and testing apps on real devices.'],
        ['IT 204', 'Computer Networking',                    'None',   'Explains how computer networks work. Includes the OSI and TCP/IP models, IP addressing, routing and switching, and basic network setup and troubleshooting.'],
        ['IT 205', 'Systems Analysis and Design',            'IT 202', 'Focuses on planning and designing information systems. Students gather requirements, model processes and data, and prepare system documentation.'],
        ['IT 206', 'Object-Oriented Programming',            'None',   'Introduces object-oriented concepts such as classes, objects, inheritance, polymorphism and encapsulation, applied through hands-on programming exercises.'],
        ['IT 207', 'Data Structures and Algorithms',         'IT 206', 'Studies how data is organized and processed efficiently. Covers arrays, linked lists, stacks, queues, trees, sorting and searching algorithms.'],
        ['IT 208', 'Human-Computer Interaction',             'IT 201', 'Looks at how people interact with software. Students learn usability principles, user research, wireframing and prototyping of user-friendly interfaces.'],
        ['IT 209', 'Information Assurance and Security',     'IT 204', 'Covers the basics of protecting information systems. Topics include common threats, authentication, encryption, secure coding practices and security policies.'],
        ['IT 210', 'Integrative Programming and Technologies', 'IT 203', 'Teaches how to combine different systems and technologies. Students build applications that connect a mobile or web front end to a back-end API and database.'],
    ],
    'BSBA' => [
        ['BA 101', 'Principles of Management',  'None',   'Introduces the core functions of management: planning, organizing, leading and controlling. Students study how organizations are structured and how managers make decisions.'],
        ['BA 102', 'Financial Accounting',      'None',   'Covers the accounting cycle and the preparation of financial statements. Students learn to record, classify, analyze and report business transactions.'],
        ['BA 103', 'Marketing Management',      'BA 101', 'Looks at how businesses understand customers and promote products. Topics include market research, consumer behavior, pricing, promotion and marketing strategy.'],
        ['BA 104', 'Business Law and Ethics',   'None',   'Explains the legal rules that affect business, such as contracts, partnerships and corporations, along with ethical standards and social responsibility in business.'],
        ['BA 105', 'Human Resource Management', 'BA 101', 'Focuses on managing people in organizations. Covers recruitment, training, performance evaluation, compensation and employee relations.'],
    ],
    'BEED' => [
        ['ED 101', 'Child and Adolescent Development', 'None',   'Studies the physical, emotional, social and cognitive growth of children and adolescents, and how development affects learning in the classroom.'],
        ['ED 102', 'Teaching Strategies',              'ED 101', 'Teaches methods and techniques for effective classroom instruction, including lesson planning, classroom management and learner engagement.'],
        ['ED 103', 'Assessment of Learning',           'ED 102', 'Covers how to design, use and interpret tests and other assessment tools to measure and improve student learning.'],
        ['ED 104', 'Principles of Teaching',           'None',   'Introduces the foundations of the teaching profession, including learning theories, the role of the teacher and professional ethics.'],
        ['ED 105', 'Curriculum Development',           'ED 102', 'Explains how school curricula are planned, organized and evaluated, and how teachers adapt lessons to learner needs.'],
    ],
];

$courses = [];
foreach ($courseTable as $program => $rows) {
    foreach ($rows as [$code, $name, $prereq, $description]) {
        $courses[] = [
            'id'           => count($courses) + 1,
            'code'         => $code,
            'name'         => $name,
            'units'        => 3,
            'prerequisite' => $prereq,
            'program_code' => $program,
            'description'  => $description,
        ];
    }
}

// The subjects a student is graded on first 5 courses of the program
$subjectsOf = fn (string $program) => array_slice($courseTable[$program] ?? [], 0, 5);

/* ---------- Grades ----------*/
$gradeTable = [
    1 => [1.25, 1.50, 1.25, 1.75, 1.50],
    2 => [1.50, 1.25, 1.75, 1.50, 2.00],
    3 => [1.75, 2.00, 1.50, 1.75, 1.50],
    4 => [1.25, 1.25, 1.50, 1.50, 1.75],
    5 => [2.00, 1.75, 2.25, 2.00, 1.75],
    6 => [1.50, 1.50, 1.25, 1.75, 1.25],
];

$grades = [];
foreach ($students as $s) {
    $program = $programOf($s);

    foreach ($subjectsOf($program) as $i => [$code, $name]) {
        $grade = $gradeTable[$s['id']][$i] ?? null;
        if ($grade === null) {
            continue; // no grade entered yet
        }

        $grades[] = [
            'id'             => $s['id'] * 100 + $i + 1,
            'student_id'     => $s['id'],
            'student_number' => $s['student_number'],
            'student_name'   => $s['name'],
            'program'        => $program,
            'course_code'    => $code,
            'course_name'    => $name,
            'units'          => 3,
            'grade'          => $grade,
            'remarks'        => $grade <= 3.00 ? 'Passed' : 'Failed',
        ];
    }
}

/* ---------- Schedules ----------*/
$dayGroups = [
    'MWF'  => ['Monday', 'Wednesday', 'Friday'],
    'TTH'  => ['Tuesday', 'Thursday'],
    'TTHS' => ['Tuesday', 'Thursday', 'Saturday'],
];

$scheduleTable = [
    'BSIT' => [
        'A' => [
            ['IT 201', 'MWF',  '8:00 AM - 9:00 AM',   'Room 201',     'Prof. Santos'],
            ['IT 202', 'TTH',  '8:00 AM - 9:30 AM',   'Room 202',     'Prof. Reyes'],
            ['IT 203', 'MWF',  '10:00 AM - 11:00 AM', 'Laboratory 1', 'Prof. Cruz'],
            ['IT 204', 'TTH',  '10:00 AM - 11:30 AM', 'Room 203',     'Prof. Garcia'],
            ['IT 205', 'TTHS', '1:00 PM - 2:00 PM',   'Room 204',     'Prof. Mendoza'],
        ],
        'B' => [
            ['IT 201', 'TTH',  '1:00 PM - 2:30 PM',   'Room 201',     'Prof. Santos'],
            ['IT 202', 'MWF',  '1:00 PM - 2:00 PM',   'Room 202',     'Prof. Reyes'],
            ['IT 203', 'TTH',  '3:00 PM - 4:30 PM',   'Laboratory 1', 'Prof. Cruz'],
            ['IT 204', 'MWF',  '2:00 PM - 3:00 PM',   'Room 203',     'Prof. Garcia'],
            ['IT 205', 'MWF',  '3:00 PM - 4:00 PM',   'Room 204',     'Prof. Mendoza'],
        ],
    ],
    'BSBA' => [
        'A' => [
            ['BA 101', 'MWF',  '9:00 AM - 10:00 AM',  'Room 301', 'Prof. Villanueva'],
            ['BA 102', 'TTH',  '9:00 AM - 10:30 AM',  'Room 302', 'Prof. Ramos'],
            ['BA 103', 'MWF',  '11:00 AM - 12:00 PM', 'Room 303', 'Prof. Bautista'],
            ['BA 104', 'TTH',  '1:00 PM - 2:30 PM',   'Room 304', 'Prof. Aquino'],
            ['BA 105', 'TTHS', '3:00 PM - 4:00 PM',   'Room 305', 'Prof. Lopez'],
        ],
        'B' => [
            ['BA 101', 'TTH',  '3:00 PM - 4:30 PM',   'Room 301', 'Prof. Villanueva'],
            ['BA 102', 'MWF',  '1:00 PM - 2:00 PM',   'Room 302', 'Prof. Ramos'],
            ['BA 103', 'TTH',  '1:00 PM - 2:30 PM',   'Room 303', 'Prof. Bautista'],
            ['BA 104', 'MWF',  '8:00 AM - 9:00 AM',   'Room 304', 'Prof. Aquino'],
            ['BA 105', 'MWF',  '2:00 PM - 3:00 PM',   'Room 305', 'Prof. Lopez'],
        ],
    ],
    'BEED' => [
        'A' => [
            ['ED 101', 'MWF',  '10:00 AM - 11:00 AM', 'Room 401', 'Prof. Dizon'],
            ['ED 102', 'TTH',  '8:00 AM - 9:30 AM',   'Room 402', 'Prof. Navarro'],
            ['ED 103', 'MWF',  '1:00 PM - 2:00 PM',   'Room 403', 'Prof. Torres'],
            ['ED 104', 'TTH',  '1:00 PM - 2:30 PM',   'Room 404', 'Prof. Flores'],
            ['ED 105', 'TTHS', '10:00 AM - 11:00 AM', 'Room 405', 'Prof. Castillo'],
        ],
        'B' => [
            ['ED 101', 'TTH',  '3:00 PM - 4:30 PM',   'Room 401', 'Prof. Dizon'],
            ['ED 102', 'MWF',  '8:00 AM - 9:00 AM',   'Room 402', 'Prof. Navarro'],
            ['ED 103', 'TTH',  '10:00 AM - 11:30 AM', 'Room 403', 'Prof. Torres'],
            ['ED 104', 'MWF',  '2:00 PM - 3:00 PM',   'Room 404', 'Prof. Flores'],
            ['ED 105', 'MWF',  '3:00 PM - 4:00 PM',   'Room 405', 'Prof. Castillo'],
        ],
    ],
];

$subjectName = collect($courses)->pluck('name', 'code')->all();

// Students of each program
$count     = [];
$schedules = [];

foreach ($students as $s) {
    $program = $programOf($s);
    $n       = $count[$program] = ($count[$program] ?? -1) + 1;
    $track   = $n % 2 === 0 ? 'A' : 'B';

    foreach ($scheduleTable[$program][$track] ?? [] as $i => [$code, $group, $time, $room, $instructor]) {
        $days = $dayGroups[$group];

        $schedules[] = [
            'id'            => $s['id'] * 100 + $i + 1,
            'student_id'    => $s['id'],
            'program_code'  => $program,
            'schedule_type' => $track,
            'code'          => $code,
            'subject'       => $subjectName[$code] ?? $code,
            'days'          => $days,
            'day'           => implode(' / ', array_map(fn ($d) => substr($d, 0, 3), $days)),
            'time'          => $time,
            'room'          => $room,
            'instructor'    => $instructor,
        ];
    }
}

/* ---------- Admin ---------- */
$admins = [[
    'id'           => 1,
    'admin_number' => 'ADM-0001',
    'name'         => 'Admin',
    'role'         => 'Administrator',
    'office'       => 'Office of the College President',
    'email'        => 'admin@occ.edu.ph',
    'phone'        => '09123456789',
    'status'       => 'Active',
    'access'       => ['Admins', 'Courses', 'Programs', 'Schedules', 'gGrades', 'Announcements', 'School'],
]];

$adminEditable = ['name', 'email', 'phone', 'office'];
$adminsPath    = storage_path('app/admin_overrides.json');
$admins        = $applyEdits($admins, $readJson($adminsPath), $adminEditable);


/*Student Routes*/

Route::get('/students', fn () => response()->json($students));

// Search by name, student number or email
Route::get('/students/search', function (Request $request) use ($students) {

    $q = strtolower(trim((string) $request->query('q', '')));

    if ($q === '') {
        return response()->json(['message' => 'Provide a search term, e.g. ?q=juan'], 422);
    }

    return response()->json(array_values(array_filter($students, fn ($s) =>
        str_contains(strtolower($s['name']), $q) ||
        str_contains(strtolower($s['student_number']), $q) ||
        str_contains(strtolower($s['email']), $q)
    )));
});

// /students/section/status
foreach (['section', 'status'] as $field) {
    Route::get("/students/$field/{value}", fn ($value) => response()->json(
        array_values(array_filter($students, fn ($s) => strcasecmp($s[$field], $value) === 0))
    ));
}

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

Route::get('/students/{id}', function ($id) use ($students, $find, $notFound) {
    $student = $find($students, $id);

    return $student ? response()->json($student) : $notFound('Student');
})->whereNumber('id');

// /students/{id}/personal, /contact, /academic, /guardian -> only some fields
$studentSections = [
    'personal' => ['student_number', 'name', 'gender', 'birth_date', 'address'],
    'contact'  => ['student_number', 'name', 'email', 'phone', 'address'],
    'academic' => ['student_number', 'name', 'course', 'year_level', 'section', 'status'],
    'guardian' => ['student_number', 'name', 'guardian', 'guardian_phone'],
];

foreach ($studentSections as $name => $fields) {
    Route::get("/students/{id}/$name", function ($id) use ($students, $fields, $find, $notFound) {
        $student = $find($students, $id);

        return $student
            ? response()->json(['id' => $student['id']] + array_intersect_key($student, array_flip($fields)))
            : $notFound('Student');
    })->whereNumber('id');
}

// Grades and schedule of ONE student
foreach (['grades' => $grades, 'schedules' => $schedules] as $name => $rows) {
    Route::get("/students/{id}/$name", function ($id) use ($students, $rows, $find, $notFound) {
        if (!$find($students, $id)) {
            return $notFound('Student');
        }

        return response()->json(array_values(array_filter($rows, fn ($r) => $r['student_id'] === (int) $id)));
    })->whereNumber('id');
}

// Update a student's profile (student_number is not editable)
$registerUpdate('students', 'Student', $students, $studentsPath, [
    'name'           => 'sometimes|required|string|max:100',
    'email'          => 'sometimes|required|email|max:100',
    'course'         => 'sometimes|required|string|max:150',
    'year_level'     => 'sometimes|required|integer|min:1|max:6',
    'phone'          => 'sometimes|nullable|string|max:20',
    'address'        => 'sometimes|nullable|string|max:200',
    'guardian'       => 'sometimes|nullable|string|max:100',
    'guardian_phone' => 'sometimes|nullable|string|max:20',
]);



 /*GRADES, SCHEDULES, COURSES, PROGRAMS*/

// /grades?student_id=1
Route::get('/grades', function (Request $request) use ($grades) {
    $id = $request->query('student_id');

    return response()->json(
        ($id === null || $id === '') ? $grades
            : array_values(array_filter($grades, fn ($g) => $g['student_id'] === (int) $id))
    );
});

// /schedules/programs/student id
Route::get('/schedules', function (Request $request) use ($schedules) {
    $program = strtoupper(trim((string) $request->query('program', '')));
    $id      = $request->query('student_id');

    return response()->json(
        collect($schedules)
            ->when($program !== '', fn ($c) => $c->where('program_code', $program))
            ->when($id !== null && $id !== '', fn ($c) => $c->where('student_id', (int) $id))
            ->values()
    );
});

Route::get('/courses',  fn () => response()->json($courses));
Route::get('/programs', fn () => response()->json($programs));

/* School Info and Announcements */

Route::get('/school', fn () => response()->json([
    'name'         => 'Opol Community College',
    'short_name'   => 'OCC',
    'motto'        => 'Education for Community Growth',
    'established'  => '1996',
    'president'    => 'Office of the College President',

    'address'      => 'Opol, Misamis Oriental, Philippines',
    'email'        => 'info@occ.edu.ph',
    'phone'        => '09123456789',
    'website'      => 'www.occ.edu.ph',
    'office_hours' => 'Monday - Friday, 8:00 AM - 5:00 PM',

    'academic_year' => '2026 - 2027',
    'semester'      => '1st Semester',

    'mission' => 'To provide accessible, quality education that develops skilled, responsible and service-oriented graduates who contribute to the growth of the community.',
    'vision'  => 'A leading community college recognized for excellence in instruction, research and service.',

    'core_values' => ['Excellence', 'Integrity', 'Service', 'Community', 'Respect'],
    'programs'    => array_values($programName),

    'stats' => [
        'students' => count($students),
        'programs' => count($programs),
        'faculty'  => 25,
    ],
]));

Route::get('/announcements', fn () => response()->json([
    ['id' => 1, 'title' => 'Enrollment Period',   'message' => 'Enrollment for the new academic year is now open.',                       'date' => 'October 1, 2026'],
    ['id' => 2, 'title' => 'Midterm Examination', 'message' => 'Midterm examinations will be held soon.',                                 'date' => 'October 15, 2026'],
    ['id' => 3, 'title' => 'School Activity',     'message' => 'Students are invited to participate in the upcoming school activity.',    'date' => 'October 20, 2026'],
]));


/* Admin Route */
Route::get('/admin',  fn () => response()->json($admins[0])); // the admin using the app
Route::get('/admins', fn () => response()->json($admins));

Route::get('/admins/{id}', function ($id) use ($admins, $find, $notFound) {
    $admin = $find($admins, $id);

    return $admin ? response()->json($admin) : $notFound('Admin');
})->whereNumber('id');

// admin_number, role, status and access are not editable
$registerUpdate('admins', 'Admin', $admins, $adminsPath, [
    'name'   => 'sometimes|required|string|max:100',
    'email'  => 'sometimes|required|email|max:100',
    'office' => 'sometimes|required|string|max:100',
    'phone'  => 'sometimes|nullable|string|max:20',
]);


/* Fallback */
Route::fallback(fn (Request $request) => response()->json([
    'message' => 'Endpoint not found: /' . $request->path(),
], 404));