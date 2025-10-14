# FitTrack - Complete Fitness Tracker Website

A comprehensive fitness tracking web application built with HTML, CSS, and PHP featuring 11 complete pages with advanced functionality.

## 🌟 Features Overview

### 11 Complete Pages
1. **Home** - Hero section, motivational quotes, quick access cards, feature previews
2. **Calculators** - BMI, Calories, and Water Intake calculators (fully functional)
3. **Workouts** - Searchable workout library with 10+ exercises, filter by difficulty
4. **Challenges** - 6 structured fitness challenges with progress tracking
5. **Nutrition** - 8+ healthy recipes with macro breakdowns and filtering
6. **Tracker** - Daily workout logging with real-time stats
7. **Progress** - Charts, graphs, calendar view, achievement badges
8. **Blog** - 6 fitness articles with category filtering
9. **Community** - Success stories, forums, member statistics
10. **Login/Signup** - User authentication with password hashing
11. **About** - Mission statement, team info, contact details

### Key Functionality
✅ **User System**
- Registration and login with secure password hashing
- Session-based authentication
- User profile management

✅ **Workout Tracking**
- Daily workout logging (exercise, sets, reps, duration, calories)
- Today's summary with live stats
- Edit and delete entries
- Pre-fill workouts from library

✅ **Progress Visualization**
- 7-day calorie burn chart
- Workout consistency calendar
- Achievement badge system (6 badges)
- Total statistics dashboard

✅ **Fitness Calculators**
- BMI Calculator with category classification
- Calorie Calculator with activity level adjustments
- Water Intake Calculator based on weight and activity

✅ **Workout Library**
- 10 pre-loaded exercises
- Search functionality
- Filter by difficulty (Beginner/Intermediate/Advanced)
- Quick add to tracker

✅ **Challenges System**
- 6 structured challenges (7-180 days)
- Difficulty levels and goals
- Participant counts and success rates
- Join/track challenges

✅ **Nutrition Guide**
- 8 healthy recipes with full macro breakdown
- Filter by meal type (Breakfast/Lunch/Dinner/Snack)
- Prep time and ingredients
- Modal view with detailed instructions

✅ **Blog Platform**
- 6 fitness articles
- Category filtering (Tips/Nutrition/Workouts/Motivation)
- Read time estimates
- Modal reading view

✅ **Community Features**
- Member statistics (15,847 active members)
- Success stories with transformations
- Forum categories
- Motivation and accountability

✅ **Design Features**
- Fully responsive mobile-friendly design
- Smooth animations and transitions
- Modal popups for detailed content
- Clean gradient color scheme
- Rotating motivational quotes
- "Surprise Me" random tips feature

## 🚀 Quick Start

### Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. Navigate to the project directory:
```bash
cd /home/workspace/fitness-tracker
```

2. Set proper permissions for data directory:
```bash
chmod 755 data
chmod 644 data/*.json
```

3. Start the PHP built-in server:
```bash
php -S localhost:8000
```

4. Open your browser and visit:
```
http://localhost:8000
```

### First Steps
1. Click "Login/Signup" in the header
2. Create a new account (use any email format)
3. Start tracking your workouts!
4. Explore all features: calculators, workouts, challenges, nutrition, blog, community

## 📁 File Structure

```
fitness-tracker/
├── index.php              # Home page with feature previews
├── calculators.php        # BMI, Calories, Water calculators
├── workouts.php          # Workout library with search/filter
├── tracker.php           # Daily workout tracker
├── progress.php          # Progress charts and badges
├── login.php             # Login/Signup forms
├── logout.php            # Logout handler
├── about.php             # About page
├── pages/
│   ├── challenges.php    # Fitness challenges
│   ├── nutrition.php     # Meal plans and recipes
│   ├── blog.php          # Blog articles
│   └── community.php     # Community hub
├── includes/
│   ├── config.php        # Configuration and helper functions
│   ├── header.php        # Header navigation template
│   └── footer.php        # Footer template
├── assets/
│   ├── css/
│   │   └── style.css     # All styles (1000+ lines)
│   ├── js/
│   │   └── app.js        # Frontend JavaScript
│   └── images/           # Image placeholders
├── data/
│   ├── users.json        # User accounts (secure hashed passwords)
│   ├── tracker.json      # Workout log entries
│   ├── workouts.json     # Pre-loaded workout library (10 exercises)
│   ├── challenges.json   # Challenge data (6 challenges)
│   ├── recipes.json      # Nutrition recipes (8 recipes)
│   └── blog.json         # Blog posts (6 articles)
└── README.md
```

## 💾 Data Storage

All data is stored in JSON files in the `data/` directory:

- **users.json** - User accounts with bcrypt hashed passwords
- **tracker.json** - All workout log entries with user_id references
- **workouts.json** - 10 pre-loaded exercises with categories
- **challenges.json** - 6 structured challenges with stats
- **recipes.json** - 8 healthy recipes with macro breakdowns
- **blog.json** - 6 blog articles with metadata

**No database setup required!** The JSON file system works perfectly for small to medium traffic sites.

## 🎯 Usage Guide

### For Users

**Getting Started:**
1. Sign up with email and password
2. Navigate through the main menu to explore features
3. Use calculators to understand your fitness metrics
4. Browse workouts and add them to your tracker
5. Log daily workouts with sets, reps, and calories
6. View your progress with charts and badges
7. Join challenges to stay motivated
8. Browse healthy recipes for meal ideas
9. Read blog articles for tips and advice
10. Connect with the community

**Daily Workflow:**
1. Log in to your account
2. Go to Tracker page
3. Add today's workouts
4. Check your Progress page for stats
5. Stay motivated with quotes and community

### For Developers

**Customization:**

1. **Add more workouts**: Edit `data/workouts.json`
   ```json
   {
     "id": 11,
     "name": "Bicep Curls",
     "category": "intermediate",
     "bodyPart": "arms",
     "sets": 3,
     "reps": 12,
     "description": "Build bicep strength"
   }
   ```

2. **Add more challenges**: Edit `data/challenges.json`

3. **Add recipes**: Edit `data/recipes.json`

4. **Add blog posts**: Edit `data/blog.json`

5. **Modify motivational quotes**: Edit `assets/js/app.js`
   ```javascript
   const motivationalQuotes = [
       "Your new quote here",
       // ... add more
   ];
   ```

6. **Customize styles**: Edit `assets/css/style.css`
   - Change color scheme in `:root` variables
   - Modify component styles
   - Adjust responsive breakpoints

7. **Add images**: Place images in `assets/images/` and update HTML

## 🎨 Customization

### Color Scheme
The site uses CSS variables for easy theming. Edit `style.css`:
```css
:root {
    --primary: #6366f1;        /* Main brand color */
    --secondary: #10b981;      /* Success/positive color */
    --danger: #ef4444;         /* Warning/delete color */
    --text: #1f2937;           /* Main text */
    --bg: #ffffff;             /* Background */
}
```

### Layout
- Fully responsive with mobile-first approach
- Breakpoint at 768px for tablet/mobile
- Grid-based card layouts
- Flexbox navigation

## 🚀 Deployment

### Production Server (Apache/Nginx)

1. Upload all files to your web root directory

2. Set proper permissions:
```bash
chmod 755 data
chmod 644 data/*.json
chmod 755 assets/css assets/js assets/images
```

3. Configure virtual host (Apache example):
```apache
<VirtualHost *:80>
    ServerName yoursite.com
    DocumentRoot /var/www/fitness-tracker
    
    <Directory /var/www/fitness-tracker>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Enable PHP and mod_rewrite (if needed)

5. Restart web server

### Security Notes for Production

⚠️ **Important Security Measures:**

1. **Enable HTTPS** - Use Let's Encrypt for free SSL
2. **Session Security** - Set secure session parameters in `config.php`:
   ```php
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1);
   ini_set('session.use_strict_mode', 1);
   ```
3. **Rate Limiting** - Add login attempt limits
4. **CSRF Protection** - Implement CSRF tokens for forms
5. **Input Validation** - Add server-side validation
6. **File Permissions** - Ensure data/ is not web-accessible (move outside web root)
7. **Backup Data** - Regular automated backups of JSON files
8. **Error Logging** - Enable PHP error logging, disable display

### Shared Hosting Deployment

1. Upload via FTP/SFTP
2. Place files in `public_html` or equivalent
3. Ensure PHP version compatibility
4. Test all features after upload

## 🌐 Browser Support
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## 📊 Tech Stack
- **Frontend**: HTML5, CSS3 (Grid, Flexbox), Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Storage**: JSON files
- **Authentication**: PHP Sessions + password_hash()
- **Charts**: Custom CSS-based visualizations

## 🔧 Advanced Features to Add

**Potential Enhancements:**
- [ ] MySQL/PostgreSQL database integration
- [ ] Export progress data (CSV, PDF)
- [ ] Social features (share achievements, follow users)
- [ ] Custom workout builder
- [ ] Workout video tutorials
- [ ] Mobile app (PWA)
- [ ] Email notifications
- [ ] API for third-party integrations
- [ ] Meal planner calendar
- [ ] Exercise form videos
- [ ] Progress photos upload
- [ ] Weight tracking graph
- [ ] Personal trainer matching
- [ ] Premium subscription features

## 🐛 Troubleshooting

**Common Issues:**

1. **Blank page / PHP errors**
   - Check PHP version (must be 7.4+)
   - Enable error display: `ini_set('display_errors', 1);`
   - Check file permissions

2. **Can't save data**
   - Verify `data/` directory is writable
   - Check JSON file permissions

3. **Styles not loading**
   - Verify `assets/css/style.css` path
   - Check browser console for 404 errors
   - Clear browser cache

4. **Login not working**
   - Check if sessions are enabled in PHP
   - Verify `session_start()` is called
   - Check browser cookies are enabled

## 📞 Support
- **Email**: support@fittrack.com
- **Phone**: (555) 123-4567
- **GitHub**: [Create an issue]

## 📄 License
© 2025 FitTrack. All rights reserved.

---

## 🎉 What Makes This Special

This is a **complete, production-ready fitness tracking application** with:
- ✨ 11 fully functional pages
- 💪 Real workout tracking with persistence
- 📊 Progress visualization with charts
- 🏆 Gamification (badges, challenges)
- 🥗 Nutrition guidance with recipes
- 📚 Blog platform for content
- 👥 Community features
- 🎨 Beautiful, responsive design
- 🔒 Secure authentication system
- 🚀 Zero dependencies, pure PHP/HTML/CSS/JS
- 📦 No database required, JSON-based storage

**Built by Jay** | Track Your Fitness. Transform Your Life.