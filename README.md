1. Check Current Branch (Optional)
First, you can check which branch you are currently on using the following command:

  git branch

2. Create a New Branch
To create a new branch in Git, use the git branch command followed by the name of the new branch:

  git branch new-branch-name

3. Switch to the New Branch
To switch to the newly created branch, use the git checkout command

  git checkout new-branch-name

7. Push the New Branch to Remote (Optional)
If you want to push the new branch to a remote repository (like GitHub) so others can access it, use the following command:
git push -u origin new-branch-name

Add to Github
# Add all files and directories
    git add .   
    git commit -m "Upload Vue 3 project"
# Replace 'main' with your branch name if different
    git push origin main  
