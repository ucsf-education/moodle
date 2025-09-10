<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Alternative login form for user accounts using manual authentication.
 *
 * @package    CLE
 * @copyright  The Regents of the University of California
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/config.php');

$logintoken = \core\session\manager::get_login_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>login</title>
</head>
<body>
<form method="post" action="/login/index.php">
    <div>
        <label for="username">
            Username:
        </label>
        <input id="username" type="text" name="username" value="">
    </div>
    <div>
        <label for="password">
            Password:
        </label>
        <input id="password" type="password" name="password" value="">
    </div>
    <div>
        <button type="submit">Log In</button>
    </div>
    <input type="hidden" name="logintoken" value="<?php echo $logintoken ?>">
</form>
</body>
</html>
