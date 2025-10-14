# FitTrack - Fitness Tracker Website

A comprehensive fitness tracking website built with PHP, MySQL, HTML, CSS, and JavaScript. Track workouts, monitor progress, set goals, and achieve your fitness objectives.

## Features

### 🏋️ Workout Management
- **Pre-built Workout Routines**: Full body, upper body, lower body, HIIT, and yoga workouts
- **Exercise Library**: Comprehensive database of exercises with instructions and tips
- **Real-time Tracking**: Track sets, reps, weight, and duration during workouts
- **Workout History**: View past workouts with performance metrics
- **Favorites System**: Save your preferred workouts for quick access

### 📊 Progress Tracking
- **Weight & Body Metrics**: Track weight, body fat percentage, and muscle mass
- **Visual Progress Charts**: See your progress over time with interactive charts
- **Goal Setting**: Set and track fitness goals with progress indicators
- **Statistics Dashboard**: View comprehensive workout and progress statistics

### 🎯 User Management
- **Secure Registration & Login**: Password hashing and session management
- **User Profiles**: Personalize your fitness journey with profile information
- **Activity Levels**: Set your activity level for accurate calorie calculations
- **Fitness Goals**: Choose from weight loss, muscle gain, maintenance, and more

### 🏆 Challenges & Motivation
- **Fitness Challenges**: Participate in community challenges
- **Progress Rewards**: Celebrate achievements and milestones
- **Motivational Content**: Daily quotes and fitness tips
- **Success Stories**: Get inspired by community achievements

### 📱 Responsive Design
- **Mobile-First**: Fully responsive design for all devices
- **Modern UI**: Clean, professional interface with smooth animations
- **Intuitive Navigation**: Easy-to-use interface for all fitness levels
- **Accessibility**: WCAG compliant design for inclusive access

## Technology Stack

- **Backend**: PHP 7.4+ with PDO for database operations
- **Database**: MySQL 5.7+ with comprehensive schema
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Security**: CSRF protection, SQL injection prevention, XSS protection
- **Architecture**: MVC pattern with API endpoints

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Step 1: Download and Setup
1. Download or clone the project files
2. Place the `fitness-tracker` folder in your web server directory
3. Ensure proper file permissions (755 for directories, 644 for files)

### Step 2: Database Configuration
1. Create a MySQL database named `fitness_tracker`
2. Update database credentials in `includes/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'fitness_tracker');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### Step 3: Database Installation
1. Visit `http://your-domain/fitness-tracker/install.php`
2. The script will automatically create all tables and insert sample data
3. **Important**: Delete or rename `install.php` after successful installation

### Step 4: Initial Setup
1. Visit `http://your-domain/fitness-tracker/register.php`
2. Create your first admin user account
3. Start using the fitness tracker!

## File Structure

```
fitness-tracker/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication API
│   ├── workouts.php       # Workout management API
│   └── progress.php       # Progress tracking API
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── app.js         # JavaScript functionality
│   └── images/            # Image assets
├── database/
│   └── schema.sql         # Database schema
├── includes/              # PHP includes
│   ├── config.php         # Configuration and utilities
│   ├── database.php       # Database connection
│   ├── header.php         # Common header
│   └── footer.php         # Common footer
├── pages/                 # Additional pages
│   ├── workouts/          # Individual workout pages
│   ├── blog.php           # Blog functionality
│   └── challenges.php     # Challenges page
├── data/                  # JSON data files (fallback)
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── workouts.php           # Workout listing
├── tracker.php            # Workout tracking
├── progress.php           # Progress dashboard
├── calculators.php        # Fitness calculators
├── about.php              # About page
└── install.php            # Database installer
```

## API Endpoints

### Authentication (`api/auth.php`)
- `POST ?action=register` - User registration
- `POST ?action=login` - User login
- `POST ?action=logout` - User logout
- `GET ?action=check` - Check authentication status
- `GET ?action=profile` - Get user profile
- `POST ?action=update_profile` - Update user profile

### Workouts (`api/workouts.php`)
- `GET ?action=list` - List workouts with filtering
- `GET ?action=get&id=X` - Get single workout with exercises
- `GET ?action=categories` - Get workout categories
- `GET ?action=exercises` - Get exercises with filtering
- `POST ?action=start_session` - Start workout session
- `POST ?action=end_session` - End workout session
- `POST ?action=log_exercise` - Log exercise performance
- `POST ?action=favorite` - Add workout to favorites
- `POST ?action=unfavorite` - Remove workout from favorites

### Progress (`api/progress.php`)
- `GET ?action=dashboard` - Get dashboard data
- `GET ?action=workout_history` - Get workout history
- `GET ?action=progress_entries` - Get progress entries
- `POST ?action=add_progress` - Add progress entry
- `GET ?action=goals` - Get user goals
- `POST ?action=add_goal` - Add new goal
- `POST ?action=update_goal` - Update goal progress
- `POST ?action=delete_goal` - Delete goal
- `GET ?action=stats` - Get user statistics
- `GET ?action=challenges` - Get available challenges

## Database Schema

### Core Tables
- **users**: User accounts and profiles
- **workout_categories**: Workout categories (Strength, Cardio, etc.)
- **workouts**: Workout routines with metadata
- **exercises**: Individual exercises with instructions
- **workout_exercises**: Many-to-many relationship between workouts and exercises

### Tracking Tables
- **workout_sessions**: User workout sessions
- **exercise_logs**: Individual exercise performance logs
- **user_progress**: Progress tracking data (weight, measurements)
- **user_goals**: User fitness goals and progress

### Additional Tables
- **challenges**: Fitness challenges
- **user_challenges**: User challenge participation
- **blog_posts**: Blog articles
- **user_favorites**: User favorite workouts

## Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **CSRF Protection**: Cross-site request forgery protection
- **SQL Injection Prevention**: Prepared statements with PDO
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session management
- **Input Validation**: Comprehensive server-side validation

## Customization

### Adding New Workouts
1. Insert into `workouts` table with proper category_id
2. Add exercises to `workout_exercises` table
3. Ensure exercises exist in `exercises` table

### Modifying UI
- Update `assets/css/style.css` for styling changes
- Modify PHP templates for layout changes
- Add JavaScript functionality in `assets/js/app.js`

### Adding Features
- Create new API endpoints in `api/` directory
- Add corresponding database tables if needed
- Update frontend pages to use new functionality

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check database credentials in `includes/database.php`
- Ensure MySQL server is running
- Verify database exists and user has proper permissions

**Installation Fails**
- Check PHP error logs
- Ensure MySQL user has CREATE privileges
- Verify PHP PDO MySQL extension is installed

**Pages Not Loading**
- Check file permissions (755 for directories, 644 for files)
- Verify web server configuration
- Check PHP error logs

**API Calls Failing**
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check PHP error logs for server-side issues

### Performance Optimization

**Database Optimization**
- Add indexes on frequently queried columns
- Use EXPLAIN to analyze query performance
- Consider database connection pooling for high traffic

**Frontend Optimization**
- Minify CSS and JavaScript files
- Optimize images and use appropriate formats
- Enable browser caching for static assets

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For support and questions:
- Check the troubleshooting section
- Review PHP and MySQL error logs
- Ensure all prerequisites are met
- Verify database configuration

## Changelog

### Version 1.0.0
- Initial release
- Complete workout tracking system
- Progress monitoring and goal setting
- User authentication and profiles
- Responsive design
- API endpoints for all functionality
- Comprehensive database schema
- Security features implemented