# ProjectX Attendance Module
### Following steps are required to active this module 
1. Clone this code from github
2. Move this code to a specific location and copy that location (cmd 'pwd' to find folder location)
3. Create .env file and update as per as .env.example
4. Set `project_dir` as folder loaction from step 2
5. Install composer on system and run `composer install` in folder location
6. Set `attendance_production_api` to get attendance to ProjectX
7. Set cronjob to run this module

### Create and start a cron job (linux)
1. Open terminal and run `crontab -e`
2. Add a new line `* * * * * /usr/bin/php {peoject_dir}/attendance.php`
3. Save and close the tab
4. run `sudo service cron restart`
   
