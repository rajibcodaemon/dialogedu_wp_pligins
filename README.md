
## DialogEDU:  Wordpress Plugin | Business Requirement Document

## Overview
This Business Requirement Document (BRD) will cover the use cases for the Word Press Plugin (WPP).  dialogEDU is a software company that offers a Learning Management System (LMS) to schools and companies world-wide.  We have a couple of customers that need a traditional e-commerce system that manages subscriptions, shopping carts and other e-commerce features.  dialogEDU’s LMS is not an e-commerce system, therefore we want to build WPP that integrates with the LMS.  Below, we will outline the known uses cases and will serve as the project requirements. 
Word Press Scope

Customers will use Wordpress as their primary customer site that will contain a membership option. It will be subscription based where a user can sign up for a Freemium tier, Paid Tier 1 and Paid Tier 2.  Content and Courses will be made available based on the selected tier.  
Content will be managed in Wordpress and the administrator will categorize the content as appropriate based on the tier structure. 
Courses are e-learning modules that will be distributed within the LMS.  The administrator will build the courses inside the LMS.  
WPP Scope
The Wordpress Plugin should serve the following use cases:
Administrators will be creating courses in the LMS.  Using the LMS API, course details like ID, name, description, price, category, tags and other fields will need to be uploaded into the Wordpress (WooCommerce) system as a product.  
The category and / or tags would be passed into WP to distinguish between membership tiers. 
When an admin creates the course in the LMS, they should be able to click on a button to “sync” the woo-commerce product listing in Wordpress.
When users sign up for a tier within Wordpress, they will be required to create an account with user information like First Name, Last Name, email, etc.  Upon membership creation, the user will also need to be created in the LMS with the attributes captured at the Wordpress membership level. 
Each course product will have a membership tier associated with the product.  When the user is created and based on their membership tier, the user will be automatically be enrolled in the LMS courses.  This will be accomplished by leveraging the LMS enrollment API.  
If a user purchases a course a standalone in Wordpress, then the user will be automatically enrolled in the LMS course.  
Single Sign On (SSO) – when users are logged into the Wordpress site, there will be a link to e-learning either on the navigation or within a content item.  If the content item is a course, then the user will be directed to the LMS.  When they get directed, they will need to be signed in based on their Wordpress membership credentials. 

## How to set up Single-Sign-On

If you want to make it possible for your users to access content in dialogEDU without having to log in directly via the login page, then you can achieve this by using the API. 
In order to make it happen you will have to follow these steps: 

Make a GET request to the dialogEDU API for a specific user 2. Redirect the users browser to the url provided with the Single Access Token field of response 
from step 1. 
The Single Access Token changes every time it is used so don’t store it anywhere. You need to make a fresh request for every login. 
How to get the dialogEDU user id 
Every user in dialogEDU has a unique id that will not change regardless of whether or not they change their email address which is what they usually login using. 
To make life easier we suggest that you store the unique user id in your own system so that you don't have to first GET the user id before each call. 
To get all of the users in your account you can make a call to GET /users which will provide a list of users including their id’s. 

Eg. https://ghe.dialogedu.com/api/v1/users 

If you want to get back one specific user then use the search variable in your querystring. 
eg. https://ghe.dialogedu.com/api/v1/users?query=test@gmail.com 

How to get the Single Access Token for a specific user 
Make a call to Get /user which will provide details about the user including their current Single Access Token 
Eg. 
https://ghe.dialogedu.com/api/v1/users/:user_id 
How to build the users SSO URL 
Step 1 - make an API call to users to retrieve the user's ID 
https://ghe.dialogedu.com/api/v1/users?query=test@gmail.com 
Step 2 - make an API call to show user to retrieve their single_access_token 
https://ghe.dialogedu.com/api/v1/users/:user_id 
Step 3 - create the SSO login link with the redirect of where you want the user to go 
(see images below to see where to get your account_url and site_key) 
:account_url/callback/:single_access_token?site=:site_key&return_to=:relative_url 
example: https://ghe.dialogedu.com/callback/xVxhAvd0GJpZE3KCbmcw?site=my- healthcare&return_to=courses 

## Contributors

1. Atindra Biswas
2. Aninda Kar
3. Moumita Ray
4. Rajib Naskar

