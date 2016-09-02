/*ALTER TABLE workout_videos DROP FOREIGN KEY workout_videos_ibfk_1 ;*/
/*ALTER TABLE workout_videos_bck DROP INDEX workout_id;*/

TRUNCATE TABLE `user_workouts`;
Drop table if exists activities, fitness_levels, program_workouts, workouts, workout_activities, workout_types, workout_videos;



CREATE TABLE IF NOT EXISTS `activities` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description1` text COLLATE utf8_unicode_ci NOT NULL,
  `description2` text COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Activities';



CREATE TABLE IF NOT EXISTS `fitness_levels` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Fitness Levels';

INSERT INTO `fitness_levels` (`id`, `name`) VALUES
('advanced', 'Advanced'),
('beginner', 'Beginner'),
('intermediate', 'Intermediate');


CREATE TABLE IF NOT EXISTS `program_workouts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `workout_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `day` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `week` int(2) unsigned NOT NULL,
  `points` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Program Workouts' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `workouts` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calorie_burned` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `workout_time` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Workouts';

CREATE TABLE IF NOT EXISTS `workout_activities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workout_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `workout_type_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activity_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activity_time` varchar(55) COLLATE utf8_unicode_ci NOT NULL,
  `activity_timer` int(11) unsigned NOT NULL,
  `calorie_burned` varchar(55) COLLATE utf8_unicode_ci NOT NULL,
  `week` int(2) unsigned NOT NULL,
  `fitness_level_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Workout Activities';

CREATE TABLE IF NOT EXISTS `workout_types` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Workout Options';

INSERT INTO `workout_types` (`id`, `name`) VALUES
('5', 'OWN WORKOUT'),
('at_home', 'At Home'),
('gym_classes', 'Gym Classes'),
('gym_machines', 'Gym Machines'),
('outdoor', 'Outdoor');

CREATE TABLE IF NOT EXISTS `workout_videos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `workout_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `video_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Workout Videos' AUTO_INCREMENT=1 ;