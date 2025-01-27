## Github commdands
# 1. Check Current Branch (Optional)
First, you can check which branch you are currently on using the following command:
```
git branch
```

# 2. Create a New Branch
To create a new branch in Git, use the git branch command followed by the name of the new branch:
```
git branch new-branch-name  
```

# 3. Switch to the New Branch
To switch to the newly created branch, use the git checkout command
```
git checkout new-branch-name
```

## 4. Push the New Branch to Remote (Optional)
If you want to push the new branch to a remote repository (like GitHub) so others can access it, use the following command:
```
git push -u origin new-branch-name
```

### Add to Github
# 5. Add all files and directories
```
git add .   
git commit -m "Upload Vue 3 project"
```
   
# 6. Replace 'main' with your branch name if different
```
git push origin main  
```
## Laravel and Vue
# 1. Install Composer Dependencies
Navigate to the laravel directory of your project in the terminal, then run the following command to install the required dependencies using Composer:
```
composer install
```

Clear Cache (if needed): After installing Composer dependencies, you might need to clear the Laravel cache:
```
php artisan config:cache
php artisan cache:clear
```

# 2. JWT_SECRET and LARAVEL APP-KEY
Run the following Artisan command to generate a random :
```
php artisan jwt:secret
php artisan key:generate
```

# 3. Retry php artisan serve
Once the Composer dependencies are successfully installed, retry running the Laravel development server:

```
php artisan serve
```
# 4. try running clinicsystem
running the Vue3 development server:
```
npm run serve
```






