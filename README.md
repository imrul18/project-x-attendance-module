# ProjectX Attendance Module
### Following steps are required to active this module 
1. Clone this code from github
2. Create .env file and update as per as .env.example
3. Move this code to a specific location and copy that location (cmd 'pwd' to find folder location)
4. Set `project_dir` as folder loaction from step 3
5. Set `machine_ip` and `machine_port` from machine.
6. Install composer on system and run `composer install` in folder location
7. Set `attendance_production_api` to get attendance to ProjectX
8. Set cronjob to run this module

### Create and start a cron job (linux)
1. Open terminal and run `crontab -e`
2. Add a new line `* * * * * /usr/bin/php {peoject_dir}/attendance.php`
3. Save and close the tab
4. run `sudo service cron restart`
   
