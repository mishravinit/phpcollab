# NOTES

## Page status
0 not checked
1 checked
2 not to check

## Test if logged is a team member of selected project
editnote
edittask
filedetail
notedetail
projectdetail
showallnotes
showalltasks
showallthreads
taskdetail
threaddetail

## Management of errors 
addclientuser
changepassword
editclient
editclientuser
edituser
login
search
sendpassword

## Permissions for a project
### project manager, owner
	- project: delete, copy, export, edit
	- tasks: add, delete, copy, add/remove to project site, view, edit
	- discussions: add, delete, close, add/remove to project site, view 
	- team: add, delete, add/remove to project site, view
	- linked content: add, delete, add/remove to project site, view, edit
### user, team member
	- tasks: add, delete, copy, add/remove to project site, view, edit
	- discussions: add, view 
	- team: view
	- linked content: add, delete, add/remove to project site, view, edit
### user or project manager (not owner), not team member
	- tasks: view
	- discussions: add, view 
	- team: view
	- linked content: view

"auto-login" (if $forcedLogin = "true")
http://localhost/phpcollab/login.php?auth=on&loginForm=admin&passwordForm=admin

### Use of the header
$headBonus = "..."; //insert additional code in <head>
$bodyCommand = "..."; //insert additional code in <body>
include("themes/".THEME."/header.php");

### Search Engine (tables and fields)
projects: pro.name, pro.description, org.name
tasks: tas.name, tas.description
members: mem.login, mem.name
organizations: org.name
discussions: top.subject
notes: note.subject, note.description

## Flag Values

### Status
0 => "Client Completed"
1 => "Completed"
2 => "Not Started"
3 => "Open"
4 => "Suspended"

### Project Active
status 0 => "Client Completed", 2 => "Not Started", 3 => "Open"

### Project Inactive
status 1 => "Completed", 4 => "Suspended"

### Profile
0 => "Administrator"
1 => "Project Manager"
2 => "User"
3 => "Client User"
4 => "Disabled Account"
5 => "Project Manager Administrator"

### Priority
0 => "None"
1 => "Very low"
2 => "Low"
3 => "Medium"
4 => "High"
5 => "Very high"

### Status Topic
0 => "Closed"
1 => "Open"

### Status Topic Bis
0 => "Yes"
1 => "No"

### Status Publish
0 => "Yes"
1 => "No"

### Status File
0 => "Approved"
1 => "Approved With Changes"
2 => "Needs Approval"
3 => "No Approvals Needed"
4 => "Not Approved"

### Support Requests
0 => "New"
1 => "Open"
2 => "Closed"

### Team Member Authorized
0 => "Yes"
1 => "No"
