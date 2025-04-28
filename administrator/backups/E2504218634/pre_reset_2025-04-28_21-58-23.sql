-- Pre-reset backup for election E2504218634
-- Created at: 2025-04-28 21:58:23



CREATE TABLE `election_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('setup','pending','active','paused','completed') NOT NULL DEFAULT 'setup',
  `election_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `control_number` varchar(20) NOT NULL DEFAULT concat('E-',year(current_timestamp()),'-',lpad(floor(rand() * 10000),4,'0')),
  PRIMARY KEY (`id`),
  UNIQUE KEY `control_number` (`control_number`),
  KEY `idx_status` (`status`,`end_time`),
  KEY `idx_election_status_control` (`control_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO election_status (id,status,election_name,created_at,end_time,last_status_change,control_number) VALUES ('1','completed','NBA Elections 2025','2025-02-12 22:30:30','2025-04-30 18:43:00','2025-04-28 21:08:48','E2504218634');


CREATE TABLE `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `election_id` int(11) NOT NULL,
  `vote_ref` varchar(20) NOT NULL,
  `votes_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`votes_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vote_ref` (`vote_ref`),
  KEY `idx_election_time` (`election_id`,`created_at`),
  CONSTRAINT `fk_vote_election` FOREIGN KEY (`election_id`) REFERENCES `election_status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('1','1','VOTE-250428-6477','{\"24\":\"75\",\"33\":\"77\",\"34\":\"81\",\"35\":\"85\",\"36\":[\"86\",\"87\"]}','2025-04-28 19:42:31');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('2','1','VOTE-250428-7173','{\"24\":\"74\",\"33\":\"79\",\"34\":\"80\",\"35\":\"85\",\"36\":[\"88\",\"91\"]}','2025-04-28 19:54:57');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('3','1','VOTE-250428-7391','{\"24\":\"76\",\"33\":\"78\",\"34\":\"81\",\"35\":\"83\",\"36\":[\"86\",\"90\"]}','2025-04-28 19:56:50');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('4','1','VOTE-250428-5747','{\"24\":\"75\",\"33\":\"78\",\"34\":\"82\",\"35\":\"83\",\"36\":[\"88\",\"91\"]}','2025-04-28 19:57:59');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('5','1','VOTE-250428-1771','{\"24\":\"75\",\"33\":\"79\",\"34\":\"81\",\"35\":\"83\",\"36\":[\"86\",\"88\"]}','2025-04-28 20:01:09');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('6','1','VOTE-250428-7631','{\"24\":\"76\",\"33\":\"78\",\"34\":\"82\",\"35\":\"83\",\"36\":[\"87\",\"90\"]}','2025-04-28 20:09:06');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('7','1','VOTE-250428-5022','{\"24\":\"75\",\"33\":\"79\",\"34\":\"80\",\"35\":\"84\",\"36\":[\"88\",\"90\"]}','2025-04-28 20:24:02');
INSERT INTO votes (id,election_id,vote_ref,votes_data,created_at) VALUES ('8','1','VOTE-250428-3664','{\"24\":\"75\",\"33\":\"79\",\"34\":\"81\",\"35\":\"84\",\"36\":[\"88\",\"90\"]}','2025-04-28 20:42:39');


CREATE TABLE `voters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `student_number` varchar(20) NOT NULL,
  `has_voted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_number` (`student_number`),
  KEY `fk_voters_course` (`course_id`),
  CONSTRAINT `fk_voters_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('94','12','202320023','1','2025-04-28 14:24:42');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('97','16','202210223','1','2025-04-28 19:26:57');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('98','16','202211775','0','2025-04-28 19:27:07');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('99','16','202211844','1','2025-04-28 19:27:14');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('100','16','202211688','1','2025-04-28 19:27:19');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('101','16','202110615','1','2025-04-28 19:28:15');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('102','16','202111184','1','2025-04-28 19:29:28');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('103','16','202211696','1','2025-04-28 20:05:09');
INSERT INTO voters (id,course_id,student_number,has_voted,created_at) VALUES ('104','14','202411986','1','2025-04-28 20:41:25');


CREATE TABLE `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `partylist_id` int(11) DEFAULT NULL,
  `photo` varchar(150) NOT NULL,
  `platform` text NOT NULL,
  `votes` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`),
  KEY `fk_candidates_partylist` (`partylist_id`),
  CONSTRAINT `fk_candidates_partylist` FOREIGN KEY (`partylist_id`) REFERENCES `partylists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('74','24','Lebron','James','15','assets/images/candidates/candidate_james_lebron.jpg','Hello, World!','1');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('75','24','Stephen','Curry','16','assets/images/candidates/candidate_curry_stephen.jpg','Hello, world!','5');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('76','24','Nikola','Jokic','17','assets/images/candidates/candidate_jokic_nikola.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('77','33','Jimmy','Butler','16','assets/images/candidates/candidate_butler_jimmy.jpg','Hello, world!','1');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('78','33','Luka','Doncic','15','assets/images/candidates/candidate_doncic_luka.jpg','Hello, world!','3');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('79','33','Russell','Westbrook','17','assets/images/candidates/candidate_westbrook_russell.jpg','Hello, world!','4');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('80','34','Jamal','Murray','17','assets/images/candidates/candidate_murray_jamal.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('81','34','Draymond','Green','16','assets/images/candidates/candidate_green_draymond.jpg','Hello, world!','4');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('82','34','Austin','Reeves','15','assets/images/candidates/candidate_reeves_austin.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('83','35','Dalton','Knecht','15','assets/images/candidates/candidate_knecht_dalton.jpg','Hello, world!','4');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('84','35','Aaron','Gordon','17','assets/images/candidates/candidate_gordon_aaron.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('85','35','Buddy','Hield','16','assets/images/candidates/candidate_hield_buddy.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('86','36','Gary','Payton II','16','assets/images/candidates/candidate_payton ii_gary.jpg','Hello, world!','3');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('87','36','Moses','Moody','16','assets/images/candidates/candidate_moody_moses.jpg','Hello, world!','2');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('88','36','Michael','Porter Jr.','17','assets/images/candidates/candidate_porter jr._michael.jpg','Hello, world!','5');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('89','36','DeAndre','Jordan','17','assets/images/candidates/candidate_jordan_deandre.jpg','Hello, world!','0');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('90','36','Bronny','James','15','assets/images/candidates/candidate_james_bronny.jpg','Hello, world!','4');
INSERT INTO candidates (id,position_id,firstname,lastname,partylist_id,photo,platform,votes) VALUES ('91','36','Rui','Hachimura','15','assets/images/candidates/candidate_hachimura_rui.jpg','Hello, world!','2');


CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO courses (id,description) VALUES ('3','Bachelor of Science in Tourism Management');
INSERT INTO courses (id,description) VALUES ('5','BSBA Major in Financial Management');
INSERT INTO courses (id,description) VALUES ('6','BSBA Major in Business Economics');
INSERT INTO courses (id,description) VALUES ('7','BSBA Major in Marketing Management');
INSERT INTO courses (id,description) VALUES ('8','BSBA Major in Human Resource Management');
INSERT INTO courses (id,description) VALUES ('10','Bachelor of Science in Mathematics');
INSERT INTO courses (id,description) VALUES ('12','Bachelor of Secondary Education');
INSERT INTO courses (id,description) VALUES ('14','Bachelor of Science in Management Accounting');
INSERT INTO courses (id,description) VALUES ('16','Bachelor of Science in Information Technology');


CREATE TABLE `partylists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO partylists (id,name) VALUES ('15','Los Angeles Lakers');
INSERT INTO partylists (id,name) VALUES ('16','Golden State Warriors');
INSERT INTO partylists (id,name) VALUES ('17','Denver Nuggets');


CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `max_vote` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO positions (id,description,max_vote,priority) VALUES ('24','President','1','1');
INSERT INTO positions (id,description,max_vote,priority) VALUES ('33','Vice President','1','2');
INSERT INTO positions (id,description,max_vote,priority) VALUES ('34','Secretary','1','3');
INSERT INTO positions (id,description,max_vote,priority) VALUES ('35','Treasurer','1','4');
INSERT INTO positions (id,description,max_vote,priority) VALUES ('36','PIO','2','5');


CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'officer',
  `gender` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO admin (id,username,email,password,firstname,lastname,photo,created_on,role,gender) VALUES ('1','janzengo','janneiljanzen.go@gmail.com','$2y$10$ucPqJTKE1TNakVubE3clfuMjfr9CAYF/MAh78ZjTsam2u2l.aNpqi','Janzen','Go','assets/images/administrators/head_go_janzen.png','2024-06-06','head','Male');
INSERT INTO admin (id,username,email,password,firstname,lastname,photo,created_on,role,gender) VALUES ('35','hamil','hamil.hackerman@gmail.com','$2y$10$nrkvEz6lph2USiWMSemyaOq24ImXw35w58qtpv.8ncCo0vS0yo97u','Hamil','Hackerman','','2025-04-28','officer','Male');
