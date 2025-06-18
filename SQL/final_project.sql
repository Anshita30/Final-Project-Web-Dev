-- MySQL dump 10.13  Distrib 8.0.34 (x86_64)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `final_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `release_year` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `duration` int(11) NOT NULL CHECK (`duration` > 0),
  `director` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL CHECK (`rating` >= 0 AND `rating` <= 10),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `release_year`, `category_id`, `duration`, `director`, `rating`, `created_at`, `updated_at`) VALUES
(1, 'Taqdeer', '<p>A gripping drama that explores the concept of destiny and free will. The story follows a young man who discovers that his life may be predetermined, and his struggle to break free from the chains of fate. With powerful performances and a thought-provoking narrative, Taqdeer delves deep into philosophical questions about choice, destiny, and human agency.</p>', 2020, 1, 142, 'Rajesh Kumar', 7.8, '2025-06-12 19:17:27', '2025-06-12 19:27:48'),
(2, 'Kal Ho Naa Ho', '<p>A heartwarming romantic drama that tells the story of Naina, a pessimistic girl whose life changes when she meets Aman, an optimistic and cheerful man. Set in New York, this film explores themes of love, friendship, and the importance of living life to the fullest. With its memorable soundtrack and emotional depth, Kal Ho Naa Ho became a beloved classic in Indian cinema.</p>', 2003, 3, 186, 'Nikkhil Advani', 8.1, '2025-06-12 19:17:27', '2025-06-12 19:27:59'),
(3, 'Inventing Anna', '<p>Based on true events, this biographical drama follows the story of Anna Delvey, a con artist who convinced New York\'s elite that she was a wealthy German heiress. The film explores themes of deception, ambition, and the American dream, showing how one woman managed to infiltrate high society through sheer audacity and manipulation.</p>', 2022, 1, 120, 'Shonda Rhimes', 7.2, '2025-06-12 19:17:27', '2025-06-12 19:28:54'),
(4, 'Harry Potter and the Philosopher\'s Stone', '<p>The magical journey begins as young Harry Potter discovers he is a wizard on his 11th birthday. Taken to Hogwarts School of Witchcraft and Wizardry, Harry learns about his famous past and begins his education in magic. This first installment of the beloved series introduces us to the wizarding world and Harry\'s quest to understand his destiny.</p>', 2001, 4, 152, 'Chris Columbus', 8.8, '2025-06-12 19:17:27', '2025-06-12 19:28:40'),
(5, 'Hi Papa', '<p>A touching family drama that explores the relationship between a father and his estranged daughter. When life takes an unexpected turn, they must reconnect and heal old wounds. This emotional journey showcases the power of forgiveness and the unbreakable bond between parent and child.</p>', 2019, 2, 128, 'Amit Sharma', 7.5, '2025-06-12 19:17:27', '2025-06-12 19:28:09'),
(6, 'Super Lover', '<p>A romantic comedy that follows the misadventures of a hopeless romantic who believes he has found his perfect match. Through a series of comedic situations and heartfelt moments, the film explores modern relationships and the lengths people go to for love.</p>', 2021, 3, 115, 'Priya Patel', 6.9, '2025-06-12 19:17:27', '2025-06-12 19:28:25'),
(7, 'Amaran', '<p>A war drama based on true events, Amaran tells the story of a soldier\'s courage and sacrifice for his country. The film portrays the challenges faced by military personnel and their families, highlighting themes of duty, honor, and patriotism.</p>', 2024, 6, 165, 'Rajkumar Periasamy', 8.2, '2025-06-12 19:17:27', '2025-06-12 19:28:35'),
(8, 'Fidaa', '<p>A romantic drama set in rural India, Fidaa tells the story of a young couple whose love faces cultural and family obstacles. The film beautifully captures traditional values while exploring themes of love, family, and cultural identity.</p>', 2017, 3, 148, 'Sekhar Kammula', 7.6, '2025-06-12 19:17:27', '2025-06-12 19:28:45'),
(9, 'Raid', '<p>Based on true events, Raid is a crime thriller that follows an honest income tax officer who conducts a raid on a powerful politician. The film showcases the fight against corruption and the courage required to stand up for justice in the face of powerful opposition.</p>', 2018, 5, 128, 'Raj Kumar Gupta', 7.4, '2025-06-12 19:17:27', '2025-06-12 19:28:55'),
(10, 'Housefull', '<p>A comedy of errors featuring multiple characters whose lives become hilariously intertwined. With mistaken identities, romantic complications, and laugh-out-loud situations, Housefull delivers non-stop entertainment and showcases the chaos that ensues when lies spiral out of control.</p>', 2010, 2, 140, 'Sajid Khan', 6.8, '2025-06-12 19:17:27', '2025-06-12 19:29:05'),
(11, 'The Last of Love', '<p>An emotional drama about finding love later in life. The story follows two individuals who meet during difficult times and discover that it\'s never too late for a second chance at happiness. This touching film explores themes of loss, healing, and the enduring power of love.</p>', 2022, 1, 112, 'Sarah Mitchell', 7.3, '2025-06-12 19:17:27', '2025-06-12 19:29:15'),
(12, 'Wicked', '<p>The untold story of the witches of Oz, Wicked reveals the friendship between Elphaba (the Wicked Witch of the West) and Glinda the Good Witch. This musical adaptation explores themes of friendship, acceptance, and the true meaning of good and evil, showing that there are always two sides to every story.</p>', 2024, 7, 160, 'Jon M. Chu', 8.5, '2025-06-12 19:17:27', '2025-06-12 19:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Drama', '2025-06-12 18:25:33', '2025-06-12 19:10:05'),
(2, 'Comedy', '2025-06-12 18:25:33', '2025-06-12 18:25:33'),
(3, 'Romance', '2025-06-12 18:25:33', '2025-06-12 19:32:26'),
(4, 'Fantasy', '2025-06-12 18:25:33', '2025-06-12 18:25:33'),
(5, 'Thriller', '2025-06-12 18:25:33', '2025-06-12 19:06:00'),
(6, 'Action', '2025-06-12 19:08:34', '2025-06-12 19:08:34'),
(7, 'Musical', '2025-06-12 19:08:34', '2025-06-12 19:08:34'),
(8, 'Biography', '2025-06-12 19:08:34', '2025-06-12 19:08:34'),
(9, 'Crime', '2025-06-12 19:08:34', '2025-06-12 19:08:34'),
(10, 'Family', '2025-06-12 19:08:34', '2025-06-12 19:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content`, `status`, `user_id`, `movie_id`, `created_at`) VALUES
(1, 'Amazing story about destiny and free will! Really makes you think.', 'visible', 2, 1, '2025-06-12 19:38:24'),
(2, 'The philosophical themes are deep and well-executed.', 'visible', 3, 1, '2025-06-12 19:38:24'),
(3, 'Rajesh Kumar did an excellent job directing this masterpiece.', 'visible', 4, 1, '2025-06-12 19:38:24'),
(4, 'One of the best Bollywood romantic dramas ever made!', 'visible', 2, 2, '2025-06-12 19:38:24'),
(5, 'Shah Rukh Khan and Preity Zinta had amazing chemistry.', 'visible', 3, 2, '2025-06-12 19:38:24'),
(6, 'The songs are still stuck in my head after all these years!', 'visible', 4, 2, '2025-06-12 19:38:24'),
(7, 'Anna Delvey\'s story is absolutely fascinating and terrifying.', 'visible', 4, 3, '2025-06-12 19:38:24'),
(8, 'Shows how easily people can be manipulated by confidence.', 'visible', 2, 3, '2025-06-12 19:38:24'),
(9, 'Julia Garner\'s performance was incredible!', 'visible', 3, 3, '2025-06-12 19:38:24'),
(10, 'The beginning of a magical journey that changed cinema forever.', 'visible', 3, 4, '2025-06-12 19:38:24'),
(11, 'Daniel Radcliffe was perfect as Harry Potter.', 'visible', 2, 4, '2025-06-12 19:38:24'),
(12, 'Hogwarts will always feel like home to me.', 'visible', 4, 4, '2025-06-12 19:38:24'),
(13, 'A heartwarming story about family reconciliation.', 'visible', 3, 5, '2025-06-12 19:38:24'),
(14, 'The emotional depth really touched my heart.', 'visible', 2, 5, '2025-06-12 19:38:24'),
(15, 'Great performances by the entire cast.', 'visible', 4, 5, '2025-06-12 19:38:24'),
(16, 'Hilarious romantic comedy with great chemistry!', 'visible', 4, 6, '2025-06-12 19:38:24'),
(17, 'Perfect date night movie with lots of laughs.', 'visible', 2, 6, '2025-06-12 19:38:24'),
(18, 'The comedic timing was spot on throughout.', 'visible', 3, 6, '2025-06-12 19:38:24'),
(19, 'A powerful tribute to our brave soldiers.', 'visible', 4, 7, '2025-06-12 19:38:24'),
(20, 'The war scenes were intense and realistic.', 'visible', 2, 7, '2025-06-12 19:38:24'),
(21, 'Truly showcases the sacrifice of military families.', 'visible', 3, 7, '2025-06-12 19:38:24'),
(22, 'Beautiful portrayal of rural Indian culture and values.', 'visible', 3, 8, '2025-06-12 19:38:24'),
(23, 'The love story felt genuine and heartfelt.', 'visible', 4, 8, '2025-06-12 19:38:24'),
(24, 'Sekhar Kammula\'s direction was excellent as always.', 'visible', 2, 8, '2025-06-12 19:38:24'),
(25, 'Ajay Devgn delivers a powerful performance as the honest officer.', 'visible', 2, 9, '2025-06-12 19:38:24'),
(26, 'Based on true events, this film shows real courage.', 'visible', 4, 9, '2025-06-12 19:38:24'),
(27, 'The fight against corruption theme is very relevant.', 'visible', 3, 9, '2025-06-12 19:38:24'),
(28, 'Non-stop laughter from start to finish!', 'visible', 2, 10, '2025-06-12 19:38:24'),
(29, 'Akshay Kumar was hilarious in this comedy of errors.', 'visible', 4, 10, '2025-06-12 19:38:24'),
(30, 'Perfect entertainment for the whole family.', 'visible', 3, 10, '2025-06-12 19:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `movie_id`, `filename`, `created_at`) VALUES
(1, 1, 'uploads/Taqdeer.jpg', '2025-06-18 19:26:52'),
(2, 2, 'uploads/675b390f4d26b-KalHoNaaHo.jpg', '2025-06-18 16:27:11'),
(3, 3, 'uploads/675b391e650e5-InventingAnna.jpg', '2025-06-18 16:27:26'),
(4, 4, 'uploads/675b392898ec6-HarryPotter.jpg', '2025-06-18 16:27:36'),
(5, 5, 'uploads/675b393428bde-HiPapa.jpg', '2025-06-18 16:27:48'),
(6, 6, 'uploads/675b393f7230b-SuperLover.jpg', '2025-06-18 16:27:59'),
(7, 7, 'uploads/675b394945fe0-Amaran.jpg', '2025-06-18 16:28:09'),
(8, 8, 'uploads/675b39590808d-Fidaa.jpg', '2025-06-18 16:28:25'),
(9, 9, 'uploads/675b39688550e-Raid.jpg', '2025-06-18 16:28:40'),
(10, 10, 'uploads/675b3976ca9f0-Housefull.jpg', '2025-06-18 16:28:54'),
(11, 11, 'uploads/675b3976ca9f1-TheLastOfLove.jpg', '2025-06-18 16:29:04'),
(12, 12, 'uploads/675b3976ca9f2-Wicked.jpg', '2025-06-18 16:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Anshita', 'Amatkan@academic.rrc.ca', 'First', 'admin', '2025-12-12 18:25:33', '2025-12-12 18:27:08'),
(2, 'useruser', 'Amatkan@academic.rrc.ca', 'password', 'user', '2025-12-12 18:28:22', '2025-06-12 18:28:22'),

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
