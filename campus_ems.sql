-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 03:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_ems`
--

-- --------------------------------------------------------

--
-- Table structure for table `create_events`
--

CREATE TABLE `create_events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(100) NOT NULL,
  `event_description` text NOT NULL,
  `date_time` datetime NOT NULL,
  `ending_time` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `date_cancelled` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `fullname` varchar(60) NOT NULL,
  `category` varchar(100) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `other_contact` varchar(255) NOT NULL,
  `related_links` text DEFAULT NULL,
  `attach_file` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `terms_accepted` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `create_events`
--

INSERT INTO `create_events` (`id`, `event_title`, `event_description`, `date_time`, `ending_time`, `status`, `date_cancelled`, `location`, `fullname`, `category`, `contact`, `other_contact`, `related_links`, `attach_file`, `user_id`, `terms_accepted`, `created_at`, `updated_at`, `reason`) VALUES
(47, 'Cavite State University - University Games 2025', 'Gear up for this year’s highly anticipated University Games on May 19-23, 2025 with the theme “Sustaining Excellence through 𝗦trength, 𝗣erseverance, 𝗢pportunity, 𝗥espect, 𝗧eamwork, and 𝗦uccess.” Students are highly encouraged to show their support by attending the games and cheering on their campus representatives. The University Games aims to foster camaraderie, sportsmanship, and pride among CvSU students and employees.', '2025-05-19 07:00:00', '2025-05-23 18:00:00', 'ended', NULL, 'Cavite State University - Don Severino Delas Alas Campus, Indang, Cavite', 'Organizer A', 'Sports', '4379505', '', '[\"https:\\\\\\\\www.facebook.com\\\\profile.php?id=61576336406987\"]', '[\"682ab192800a8_ugames2025.jpg\"]', 10, 0, '2025-05-19 04:20:34', NULL, ''),
(49, '4th Association of Computer Engineering (ACES) Day', '𝙍𝙚𝙖𝙙𝙮, 𝙎𝙚𝙩, 𝙄𝙣𝙣𝙤𝙫𝙖𝙩𝙚! Tatakbo tayo hindi lang para manalo—but also to Achieve, Create and Evolve in the world of technology. Get ready to gain something new this ACES DAY, this is the race you don’t wanna miss!', '2025-05-08 07:00:00', '2025-05-09 19:00:00', 'ended', NULL, 'Covered Court 2', 'Organizer A', 'Celebration', '01696969', '', '[\"https:\\/\\/www.facebook.com\\/ACESCVSUCCAT\",\"https:\\/\\/twibbo.nz\\/dpblast-aces\"]', '[\"682acb6d7ac79_aces2025.png\"]', 10, 0, '2025-05-19 06:10:53', NULL, ''),
(54, 'testing03', 'afaa', '2025-05-29 14:39:00', '2025-06-07 14:39:00', 'active', NULL, 'kahit saan', 'Organizer A', 'Workshop', '41241241241', '', '[]', '[\"6834fb942532b_ac2.jpg\"]', 10, 0, '2025-05-25 06:39:34', '2025-05-27 07:39:00', ''),
(62, 'timer', 'arasrfa', '2025-05-28 21:18:00', '2025-05-28 22:16:00', 'ongoing', NULL, 'kahit saan', 'Organizer A', 'Cultural', '32452424242', '', '[]', NULL, 10, 1, '2025-05-28 13:16:21', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inbox`
--

CREATE TABLE `inbox` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject_type` enum('report','request','feedback','question','support','other','event','user') NOT NULL,
  `subject_custom` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `attach_file` text DEFAULT NULL,
  `status` enum('unread','read','pending','responded') DEFAULT 'unread',
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `reportuser_id` int(11) NOT NULL,
  `reply` text NOT NULL,
  `reply_file` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `replied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inbox`
--

INSERT INTO `inbox` (`id`, `title`, `subject_type`, `subject_custom`, `message`, `attach_file`, `status`, `user_id`, `event_id`, `reportuser_id`, `reply`, `reply_file`, `created_at`, `replied_at`) VALUES
(7, 'testing', 'event', 'Cavite State University - University Games 2025', 'yoko pumunta', '', 'responded', 13, 47, 0, 'Edi Wag', NULL, '2025-05-19 10:11:16', '2025-05-24 17:08:02'),
(8, 'testing 02', 'request', 'Upgrade to Organizer', 'pa accept', '', 'pending', 13, 0, 0, 'yoko', NULL, '2025-05-19 10:39:49', '2025-05-24 17:22:18'),
(11, 'testing05', 'feedback', '', '<p>Lorem ipsum dolor sit amet. Et consequuntur consectetur et accusamus consequatur et corporis ducimus a omnis ullam. Eum officiis debitis quo exercitationem enim est quod quas a ipsa distinctio qui architecto rerum ut dolores minus quo facere galisum. </p><p>Qui enim dolorem ut dolor iusto qui nesciunt maiores ea numquam necessitatibus. Est impedit atque quo nisi voluptatum qui incidunt similique non corporis deleniti et odio illo qui sequi sunt et sapiente laudantium. Ex velit architecto eum dolorem facere sed modi blanditiis sit eligendi galisum ea voluptas porro qui corrupti molestiae sed ipsam optio. Ex placeat omnis et asperiores tempora ut tenetur delectus rem minus doloremque. </p><p>Ut doloremque incidunt ut omnis necessitatibus aut molestiae tempora. Ab similique veritatis qui necessitatibus sint ea nostrum consequuntur et Quis odit et molestias animi. Id enim tempora quo exercitationem explicabo in doloribus praesentium. Quo laboriosam cupiditate et numquam quis aut sint adipisci. </p>', '', 'unread', 13, 0, 0, '', NULL, '2025-05-26 11:30:01', NULL),
(12, 'reportuser01', 'other', 'User', 'afafasfa', '', 'responded', 10, 0, 13, 'sgfasjgnkasgas', NULL, '2025-05-26 15:09:49', '2025-05-27 13:38:30');

-- --------------------------------------------------------

--
-- Table structure for table `registers`
--

CREATE TABLE `registers` (
  `id` int(11) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `section` varchar(20) NOT NULL,
  `student_number` int(9) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `terms_accepted` tinyint(1) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registers`
--

INSERT INTO `registers` (`id`, `firstname`, `lastname`, `year_level`, `section`, `student_number`, `contact_number`, `email`, `terms_accepted`, `registration_date`, `user_id`, `event_id`) VALUES
(13, 'Attendee', 'A', '2nd Year', 'BSBA 102A', 214141244, '41241241241', 'attendeeA@gmail.com', 1, '2025-05-19 07:14:11', 13, 47),
(15, 'Attendee', 'A', '2nd Year', 'BSBA 102B', 214242424, '1696969242', 'blblbl@gmail.com', 1, '2025-05-28 13:06:01', 13, 54),
(16, 'Attendee', 'A', '2nd Year', 'BSBA 102A', 324524525, '41241241241', 'ttwtw@gmail.com', 1, '2025-05-28 13:16:46', 13, 62);

-- --------------------------------------------------------

--
-- Table structure for table `usertable`
--

CREATE TABLE `usertable` (
  `id` int(11) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `role` enum('attendee','admin','organizer','pending','banned') NOT NULL DEFAULT 'attendee',
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `code` mediumint(50) NOT NULL,
  `status` text NOT NULL,
  `about` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_at` datetime DEFAULT NULL,
  `banned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertable`
--

INSERT INTO `usertable` (`id`, `firstname`, `lastname`, `role`, `username`, `email`, `contact`, `organization`, `password`, `code`, `status`, `about`, `created_at`, `changed_at`, `banned_at`) VALUES
(9, 'Admin', 'Admin', 'admin', 'admin', 'admin@example.com', '', '', '$2y$10$j21qiuT.geI4UjGb8KnjiO5t6qtkhYuB1V0Fco/nNRNIQcL2yYTme', 0, 'verified', '', '2025-05-18 09:22:46', NULL, NULL),
(10, 'Organizer', 'A', 'organizer', 'organizer_A', 'organizer@example.com', '35315325235', 'ACES', '$2y$10$j21qiuT.geI4UjGb8KnjiO5t6qtkhYuB1V0Fco/nNRNIQcL2yYTme', 0, 'verified', 'sfsfsfs', '2025-05-18 09:25:27', '2025-05-27 13:39:43', '2025-05-27 12:49:07'),
(13, 'Attendee', 'A', 'attendee', 'attendee_A', 'attendee@example.com', '35315325235', 'ACES', '$2y$10$j21qiuT.geI4UjGb8KnjiO5t6qtkhYuB1V0Fco/nNRNIQcL2yYTme', 0, 'verified', 'Lorem ipsum dolor sit amet, hinc elaboraret eam ne, illum nihil appareat sed ut. Usu te animal labores perpetua. Eam at accusam delicatissimi conclusionemque, brute ornatus recteque ea qui. Ei vis dictas vivendo. An iudico platonem sea, ei ius nostrum mediocrem constituto, sea cu euismod accusam accusata. Ipsum velit voluptatibus per an. Enim accommodare an quo, vix in putent fuisset assentior. Quo ridens alterum tincidunt cu, ea quo detracto democritum. Errem disputationi conclusionemque et ', '2025-05-18 09:28:34', '2025-05-27 12:57:37', '2025-05-27 12:20:56'),
(14, 'B', 'Organizer', 'organizer', 'organizer_B', 'organizerB@example.com', '', 'AHMS', '$2y$10$j21qiuT.geI4UjGb8KnjiO5t6qtkhYuB1V0Fco/nNRNIQcL2yYTme', 0, 'verified', '', '2025-05-18 13:47:10', '2025-05-28 01:35:25', '2025-05-25 03:12:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `create_events`
--
ALTER TABLE `create_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inbox`
--
ALTER TABLE `inbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `registers`
--
ALTER TABLE `registers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_event` (`event_id`);

--
-- Indexes for table `usertable`
--
ALTER TABLE `usertable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `create_events`
--
ALTER TABLE `create_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `inbox`
--
ALTER TABLE `inbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `registers`
--
ALTER TABLE `registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `usertable`
--
ALTER TABLE `usertable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inbox`
--
ALTER TABLE `inbox`
  ADD CONSTRAINT `inbox_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usertable` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registers`
--
ALTER TABLE `registers`
  ADD CONSTRAINT `fk_event` FOREIGN KEY (`event_id`) REFERENCES `create_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `usertable` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_event_status` ON SCHEDULE EVERY 5 SECOND STARTS '2025-05-28 20:32:13' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
      UPDATE create_events
      SET status = 'ongoing'
      WHERE date_time <= NOW() AND ending_time > NOW() AND status NOT IN ('ongoing', 'cancelled');

      UPDATE create_events
      SET status = 'ended'
      WHERE ending_time < NOW() AND status NOT IN ('ended', 'cancelled');

      UPDATE create_events
      SET status = 'active'
      WHERE date_time > NOW() AND status NOT IN ('active', 'cancelled');
    END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
