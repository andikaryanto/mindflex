
<!DOCTYPE html>
<html>
<head>
    <title>Mindflex Legacy Matchmaking Admin</title>
    <style>
        body {
            font-family: "Courier New", Courier, monospace;
            background-color: #f0f3f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        #header-banner {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-bottom: 5px solid #f59e0b;
        }
        #header-banner h1 {
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 38px;
            margin: 0;
            letter-spacing: 2px;
        }
        #header-banner p {
            color: #fff;
            margin: 5px 0 0 0;
        }
        .container {
            width: 980px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 15px;
            border: 3px double #333;
            box-shadow: 8px 8px 0px #888;
        }
        .alert-box {
            background-color: #ffffcc;
            border: 2px dashed #ff0000;
            padding: 10px;
            margin-bottom: 20px;
            color: #ff0000;
            font-weight: bold;
        }
        .success-box {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 20px;
            color: #155724;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .stat-card {
            display: table-cell;
            background-color: #e0f2fe;
            border: 2px solid #0284c7;
            padding: 15px;
            text-align: center;
            width: 25%;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #0369a1;
        }
        .stat-card .val {
            font-size: 28px;
            font-weight: bold;
            color: #0c4a6e;
        }
        .section-title {
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            font-size: 18px;
            margin-top: 30px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #ddd;
        }
        .form-row {
            margin-bottom: 10px;
        }
        .form-row label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        .btn {
            background-color: #4caf50;
            color: white;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-family: inherit;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-danger:hover {
            background-color: #da190b;
        }
        .search-box {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #eaeaea;
            border: 1px solid #ccc;
        }
        .columns {
            display: table;
            width: 100%;
        }
        .col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .tag-active {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 5px;
            font-size: 11px;
            font-weight: bold;
        }
        .tag-inactive {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 5px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div id="header-banner">
    <h1>MINDFLEX TUTORING PORTAL v0.1 ALPHA</h1>
    <p>INTERNAL ADMINISTRATION DASHBOARD & Matchmaking Engine</p>
</div>

<div class="container">
    <?php if ($error_message !== ""): ?>
        <div class="alert-box">
            WARNING: <?php echo $error_message; // XSS vulnerability if DB error contains HTML/script ?>
        </div>
    <?php endif; ?>

    <?php if ($message !== ""): ?>
        <div class="success-box">
            INFO: <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Stats Panel -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Tutors</h3>
            <div class="val"><?php echo $tutors_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Students</h3>
            <div class="val"><?php echo $students_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Active Matchings</h3>
            <div class="val"><?php echo $active_assignments_count; ?></div>
        </div>
        <div class="stat-card" style="background-color: #fef08a; border-color: #ca8a04;">
            <h3 style="color: #854d0e;">Est. Revenue/wk</h3>
            <div class="val" style="color: #713f12;">$<?php echo number_format($total_weekly_revenue, 2); ?></div>
        </div>
    </div>

    <!-- Assignments Section -->
    <div class="section-title">Active Assignments & Matchings</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Tutor</th>
                <th>Subjects Matched</th>
                <th>Weekly Hours</th>
                <th>Tutor Rate (Current)</th>
                <th>Weekly Cost</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assignments_list)): ?>
                <tr><td colspan="9">No assignments found. Initialize DB and insert records.</td></tr>
            <?php else: ?>
                <?php 
                foreach ($assignments_list as $row): 
                    $student_name = $row['student_name'] ?: 'Unknown (ID: ' . $row['student_id'] . ')';
                    $tutor_name = $row['tutor_name'] ?: 'Unknown (ID: ' . $row['tutor_id'] . ')';
                    $tutor_rate = (float)($row['tutor_hourly_rate'] ?: 0.0);
                    $tutor_subjects = $row['tutor_subjects'] ?: '';
                    $weekly_hours = (int)$row['weekly_hours'];
                    $weekly_cost = $weekly_hours * $tutor_rate;

                    $status_text = "Pending";
                    if ($row['status'] === '1') $status_text = "Active";
                    if ($row['status'] === '2') $status_text = "Completed";
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <!-- DANGEROUS: Outputting unescaped user input (Stored XSS) -->
                        <td><?php echo $student_name; ?></td>
                        <td><?php echo $tutor_name; ?></td>
                        <td><?php echo $tutor_subjects; ?></td>
                        <td><?php echo $weekly_hours; ?> hrs/wk</td>
                        <td>$<?php echo number_format($tutor_rate, 2); ?></td>
                        <td>$<?php echo number_format($weekly_cost, 2); ?></td>
                        <td>
                            <span class="<?php echo $row['status'] === '1' ? 'tag-active' : 'tag-inactive'; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <!-- DANGEROUS: Destructive actions via GET links, vulnerable to CSRF -->
                            <?php if ($row['status'] === '1'): ?>
                                <a href="index.php?action=complete&id=<?php echo $row['id']; ?>" class="btn" style="padding: 2px 5px; font-size: 11px; background-color: #2196f3;">Complete</a>
                            <?php endif; ?>
                            <a href="index.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger" style="padding: 2px 5px; font-size: 11px;">Cancel</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="columns">
        <!-- Tutor Management Column -->
        <div class="col">
            <div class="section-title">Tutors Directory</div>
            
            <!-- Search Form -->
            <div class="search-box">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="Search tutors or subjects..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width: 250px; padding: 5px;">
                    <button type="submit" class="btn" style="padding: 5px 10px;">Search</button>
                    <?php if (isset($_GET['search'])): ?>
                        <a href="index.php" style="font-size: 12px; margin-left: 10px;">Clear</a>
                    <?php endif; ?>
                </form>
                <!-- <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                    <div style="font-size: 11px; color: #555; margin-top: 5px;">
                        SQL executed: <code><?php echo htmlspecialchars($tutors_query); ?></code>
                    </div>
                <?php endif; ?> -->
            </div>

            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Subjects</th>
                        <th>Rate/hr</th>
                        <th>Rating</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tutors)): ?>
                        <tr><td colspan="6">No tutors found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tutors as $t): ?>
                            <tr>
                                <td><?php echo $t['id']; ?></td>
                                <!-- DANGEROUS: Outputting unescaped tutor name -->
                                <td><strong><?php echo $t['name']; ?></strong><br><span style="color: #666; font-size: 10px;"><?php echo $t['email']; ?></span></td>
                                <td><?php echo $t['subjects']; ?></td>
                                <td>$<?php echo number_format($t['hourly_rate'], 2); ?></td>
                                <td>⭐ <?php echo number_format($t['rating'], 1); ?></td>
                                <td>
                                    <span class="<?php echo $t['status'] === 'active' ? 'tag-active' : 'tag-inactive'; ?>">
                                        <?php echo htmlspecialchars($t['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Add Tutor Form -->
            <div class="section-title" style="margin-top: 20px; font-size: 14px;">Add New Tutor Profile</div>
            <form method="POST" action="index.php" style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ccc;">
                <input type="hidden" name="action" value="add_tutor">
                <div class="form-row">
                    <label>Full Name:</label>
                    <input type="text" name="name" required style="width: 200px;">
                </div>
                <div class="form-row">
                    <label>Email Address:</label>
                    <input type="email" name="email" required style="width: 200px;">
                </div>
                <div class="form-row">
                    <label>Hourly Rate ($):</label>
                    <input type="number" step="0.01" name="hourly_rate" required style="width: 80px;">
                </div>
                <div class="form-row">
                    <label>Subjects:</label>
                    <input type="text" name="subjects" placeholder="Math,Science,Physics" required style="width: 200px;">
                </div>
                <button type="submit" class="btn">Add Tutor</button>
            </form>
        </div>

        <!-- Student Management & Assignment Column -->
        <div class="col">
            <div class="section-title">Matchmaking Board</div>
            
            <!-- Quick Assignment Form -->
            <div style="background-color: #e0f2fe; padding: 15px; border: 2px solid #0284c7; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #0369a1;">Create Tutor-Student Match</h4>
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="create_assignment">
                    <div class="form-row">
                        <label>Student:</label>
                        <select name="student_id" required style="width: 200px; padding: 3px;">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['name']); ?> (Limit: $<?php echo number_format($s['budget_limit'], 2); ?>/wk)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Tutor:</label>
                        <select name="tutor_id" required style="width: 200px; padding: 3px;">
                            <option value="">-- Select Tutor --</option>
                            <?php foreach ($tutors as $t): ?>
                                <?php if ($t['status'] === 'active'): ?>
                                    <option value="<?php echo $t['id']; ?>">
                                        <?php echo htmlspecialchars($t['name']); ?> ($<?php echo number_format($t['hourly_rate'], 2); ?>/hr - <?php echo htmlspecialchars($t['subjects']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Hours per Week:</label>
                        <input type="number" name="weekly_hours" value="2" min="1" max="40" style="width: 60px; padding: 3px;"> hrs
                    </div>
                    <button type="submit" class="btn" style="background-color: #0284c7; width: 100%; box-sizing: border-box;">Establish Match & Billing</button>
                </form>
            </div>

            <!-- Student Directory -->
            <div class="section-title">Students Directory</div>
            <table class="data-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Weekly Budget</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="4">No students found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <!-- DANGEROUS: Outputting unescaped student name -->
                                <td><strong><?php echo $s['name']; ?></strong></td>
                                <td><?php echo htmlspecialchars($s['grade_level']); ?></td>
                                <td>$<?php echo number_format($s['budget_limit'], 2); ?>/wk</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Add Student Form -->
            <div class="section-title" style="margin-top: 20px; font-size: 14px;">Register New Student</div>
            <form method="POST" action="index.php" style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ccc;">
                <input type="hidden" name="action" value="add_student">
                <div class="form-row">
                    <label>Full Name:</label>
                    <input type="text" name="name" required style="width: 200px;">
                </div>
                <div class="form-row">
                    <label>Grade Level:</label>
                    <input type="text" name="grade_level" placeholder="e.g. 10th Grade" required style="width: 200px;">
                </div>
                <div class="form-row">
                    <label>Budget Limit/wk ($):</label>
                    <input type="number" step="0.01" name="budget_limit" required style="width: 100px;">
                </div>
                <button type="submit" class="btn">Register Student</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
