-- Fitness Tracker Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS fitness_tracker;
USE fitness_tracker;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    height DECIMAL(5,2), -- in cm
    weight DECIMAL(5,2), -- in kg
    activity_level ENUM('sedentary', 'light', 'moderate', 'active', 'very_active'),
    fitness_goal ENUM('weight_loss', 'muscle_gain', 'maintenance', 'endurance', 'strength'),
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Workout categories
CREATE TABLE workout_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Workouts table
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'expert'),
    duration_minutes INT,
    calories_burned INT,
    equipment_needed TEXT,
    instructions TEXT,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES workout_categories(id) ON DELETE SET NULL
);

-- Exercises table
CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    muscle_groups JSON, -- Array of muscle groups
    equipment_needed VARCHAR(255),
    instructions TEXT,
    tips TEXT,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    difficulty_level ENUM('beginner', 'intermediate', 'advanced'),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Workout exercises (many-to-many relationship)
CREATE TABLE workout_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets INT DEFAULT 1,
    reps VARCHAR(20), -- Can be "10", "10-12", "30s", etc.
    weight DECIMAL(5,2), -- Optional weight
    rest_seconds INT DEFAULT 60,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    UNIQUE KEY unique_workout_exercise (workout_id, exercise_id, order_index)
);

-- User workout sessions
CREATE TABLE workout_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    duration_minutes INT,
    calories_burned INT,
    notes TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE
);

-- Exercise logs (individual exercise performance)
CREATE TABLE exercise_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets_completed INT,
    reps_completed VARCHAR(20),
    weight_used DECIMAL(5,2),
    duration_seconds INT, -- For time-based exercises
    rest_taken_seconds INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES workout_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- User progress tracking
CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    measurement_date DATE NOT NULL,
    weight DECIMAL(5,2),
    body_fat_percentage DECIMAL(4,2),
    muscle_mass DECIMAL(5,2),
    measurements JSON, -- Body measurements (chest, waist, arms, etc.)
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User goals
CREATE TABLE user_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_type ENUM('weight_loss', 'weight_gain', 'muscle_gain', 'endurance', 'strength', 'flexibility'),
    target_value DECIMAL(8,2),
    current_value DECIMAL(8,2) DEFAULT 0,
    unit VARCHAR(20),
    target_date DATE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Challenges table
CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    challenge_type ENUM('workout_streak', 'weight_loss', 'muscle_gain', 'endurance', 'custom'),
    target_value DECIMAL(8,2),
    unit VARCHAR(20),
    duration_days INT,
    difficulty_level ENUM('easy', 'medium', 'hard', 'expert'),
    reward_description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User challenge participation
CREATE TABLE user_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    challenge_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    progress_value DECIMAL(8,2) DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
);

-- Blog posts
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    author_id INT,
    category VARCHAR(50),
    tags JSON,
    featured_image VARCHAR(255),
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- User favorites
CREATE TABLE user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_id INT,
    exercise_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    CHECK (workout_id IS NOT NULL OR exercise_id IS NOT NULL)
);

-- Insert default workout categories
INSERT INTO workout_categories (name, description, icon) VALUES
('Strength Training', 'Build muscle and increase strength', '💪'),
('Cardio', 'Improve cardiovascular fitness', '🏃'),
('Flexibility', 'Improve flexibility and mobility', '🧘'),
('HIIT', 'High-intensity interval training', '⚡'),
('Yoga', 'Mind-body connection and flexibility', '🧘‍♀️'),
('Pilates', 'Core strength and stability', '🤸'),
('Sports', 'Sport-specific training', '⚽'),
('Rehabilitation', 'Injury recovery and prevention', '🏥');

-- Insert sample workouts
INSERT INTO workouts (name, description, category_id, difficulty_level, duration_minutes, calories_burned, equipment_needed, instructions, image_url) VALUES
('Full Body Strength', 'Complete workout targeting all major muscle groups', 1, 'intermediate', 45, 350, 'Dumbbells, Bench, Barbell', 'Focus on compound movements with proper form', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop'),
('Upper Body Power', 'Build strength in chest, back, shoulders, and arms', 1, 'beginner', 30, 250, 'Dumbbells, Bench', 'Balanced push-pull routine', 'https://images.unsplash.com/photo-1558611848-73f7eb4001a1?q=80&w=1200&auto=format&fit=crop'),
('Lower Body Blast', 'Target legs, glutes, and core for power', 1, 'intermediate', 40, 400, 'Barbell, Dumbbells', 'Focus on controlled movements', 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=1200&auto=format&fit=crop'),
('HIIT Cardio', 'High-intensity intervals for maximum calorie burn', 4, 'advanced', 25, 500, 'None', 'Push to maximum effort during work intervals', 'https://images.unsplash.com/photo-1526406915894-6c4aa1b54f09?q=80&w=1200&auto=format&fit=crop'),
('Yoga Flow', 'Improve flexibility and mindfulness', 5, 'beginner', 30, 150, 'Yoga Mat', 'Move with your breath', 'https://images.unsplash.com/photo-1571019613914-85f342c55f38?q=80&w=1200&auto=format&fit=crop');

-- Insert sample exercises
INSERT INTO exercises (name, description, muscle_groups, equipment_needed, instructions, tips, difficulty_level) VALUES
('Push-ups', 'Classic upper body exercise', '["chest", "triceps", "shoulders"]', 'None', 'Start in plank position, lower body to ground, push back up', 'Keep core engaged, don\'t let hips sag', 'beginner'),
('Squats', 'Build strong legs and glutes', '["quadriceps", "glutes", "hamstrings"]', 'None', 'Stand with feet shoulder-width apart, lower as if sitting in chair', 'Keep knees over toes, chest up', 'beginner'),
('Plank', 'Core stability exercise', '["core", "shoulders"]', 'None', 'Hold body in straight line on forearms and toes', 'Don\'t let hips sag or pike up', 'beginner'),
('Bench Press', 'Build chest strength', '["chest", "triceps", "shoulders"]', 'Barbell, Bench', 'Lie on bench, lower bar to chest, press up', 'Keep feet flat on floor, retract shoulder blades', 'intermediate'),
('Deadlifts', 'Full body power exercise', '["hamstrings", "glutes", "back", "core"]', 'Barbell', 'Hinge at hips, keep bar close to legs', 'Maintain neutral spine, drive through heels', 'advanced'),
('Pull-ups', 'Upper body pull strength', '["lats", "biceps", "rhomboids"]', 'Pull-up Bar', 'Hang from bar, pull body up until chin over bar', 'Use full range of motion, control the descent', 'intermediate'),
('Lunges', 'Single leg strength', '["quadriceps", "glutes", "hamstrings"]', 'None', 'Step forward, lower back knee toward ground', 'Keep front knee over ankle, torso upright', 'beginner'),
('Shoulder Press', 'Build shoulder strength', '["shoulders", "triceps", "core"]', 'Dumbbells', 'Press weights overhead from shoulder height', 'Keep core engaged, don\'t arch back excessively', 'intermediate'),
('Burpees', 'High intensity full body', '["full-body", "cardio"]', 'None', 'Squat, jump back to plank, do push-up, jump forward, jump up', 'Modify by stepping instead of jumping', 'advanced'),
('Russian Twists', 'Oblique strengthening', '["obliques", "core"]', 'None', 'Sit with knees bent, lean back, rotate torso side to side', 'Keep core engaged, don\'t let back round', 'intermediate');

-- Insert workout-exercise relationships
INSERT INTO workout_exercises (workout_id, exercise_id, sets, reps, rest_seconds, order_index) VALUES
-- Full Body Strength
(1, 2, 3, '8-10', 90, 1),  -- Squats
(1, 4, 3, '8-10', 90, 2),  -- Bench Press
(1, 6, 3, '6-10', 90, 3),  -- Pull-ups
(1, 5, 3, '6-8', 120, 4),  -- Deadlifts
(1, 8, 3, '8-10', 90, 5),  -- Shoulder Press
(1, 3, 3, '45s', 60, 6),   -- Plank

-- Upper Body Power
(2, 4, 3, '8-12', 90, 1),  -- Bench Press
(2, 6, 3, '6-10', 90, 2),  -- Pull-ups
(2, 8, 3, '8-10', 90, 3),  -- Shoulder Press
(2, 1, 2, '12-15', 60, 4), -- Push-ups
(2, 7, 2, '10-12', 60, 5), -- Lunges (for balance)

-- Lower Body Blast
(3, 2, 4, '5-8', 120, 1),  -- Squats
(3, 5, 3, '6-8', 120, 2),  -- Deadlifts
(3, 7, 3, '10-12', 90, 3), -- Lunges
(3, 3, 3, '60s', 60, 4),   -- Plank

-- HIIT Cardio
(4, 9, 3, '30s', 60, 1),   -- Burpees
(4, 1, 3, '30s', 60, 2),   -- Push-ups
(4, 2, 3, '30s', 60, 3),   -- Squats
(4, 10, 3, '30s', 60, 4),  -- Russian Twists

-- Yoga Flow
(5, 3, 1, '2min', 0, 1),   -- Plank (modified)
(5, 2, 1, '1min', 0, 2),   -- Squats (modified)
(5, 7, 1, '1min', 0, 3);   -- Lunges (modified)

-- Insert sample challenges
INSERT INTO challenges (name, description, challenge_type, target_value, unit, duration_days, difficulty_level, reward_description, start_date, end_date) VALUES
('30-Day Push-up Challenge', 'Complete 100 push-ups every day for 30 days', 'workout_streak', 100, 'push-ups', 30, 'medium', 'Improved upper body strength and endurance', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Summer Body Challenge', 'Lose 10 pounds in 8 weeks', 'weight_loss', 10, 'pounds', 56, 'hard', 'Confidence boost and healthier lifestyle', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 56 DAY)),
('Flexibility Master', 'Hold a 5-minute plank by the end of the month', 'endurance', 300, 'seconds', 30, 'expert', 'Incredible core strength and mental toughness', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- Insert sample blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, is_published, published_at) VALUES
('10 Beginner Mistakes to Avoid', '10-beginner-mistakes-to-avoid', 'Learn the most common gym mistakes and how to prevent them from slowing your progress.', '<p>Starting your fitness journey can be overwhelming, but avoiding these common mistakes will set you up for success...</p>', 'Fitness Tips', '["beginner", "mistakes", "tips"]', TRUE, NOW()),
('The Science Behind Muscle Growth', 'science-behind-muscle-growth', 'Understanding hypertrophy will help you train smarter and see better results.', '<p>Muscle growth, or hypertrophy, is a complex process that involves multiple factors...</p>', 'Science', '["muscle growth", "hypertrophy", "science"]', TRUE, NOW()),
('Nutrition Timing Tips', 'nutrition-timing-tips', 'When to eat for optimal performance and recovery throughout your day.', '<p>Timing your nutrition properly can make a significant difference in your performance and recovery...</p>', 'Nutrition', '["nutrition", "timing", "performance"]', TRUE, NOW());
