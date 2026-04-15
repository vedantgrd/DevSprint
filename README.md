# Hackathon Discovery and Registration Web Application

![Flowchart](images/flowchart.jpeg)

## Overview

The **Hackathon Discovery and Registration Web Application** is a centralized, web-based platform designed to streamline the process of organizing, discovering, and participating in hackathons. In recent years, hackathons have emerged as an important medium for innovation, collaboration, and skill development. However, information about such events is often scattered. This project bridges the gap by providing a central interface tailored for both **participants** and **organizers (admins)**.

### Abstract 
The application leverages HTML, CSS, JavaScript, and PHP. It provides a unique location-based search using OpenStreetMap API that enables users to find hackathons strictly in their nearby areas (50 km radius) by simply entering their city. Participants can apply for hackathons seamlessly and present their linked-in integrated profile for admins to review and accept/reject. 

---

## Technical Stack
- **Frontend Technologies:** HTML, CSS, JavaScript
- **Backend Technology:** PHP
- **Database:** MySQL
- **APIs Used:** OpenStreetMap API (Location search and mapping)

---

## Features & System Modules

### 1. User Management (Authentication & Profile)
Allows users and administrators to securely register, login, and maintain their profiles. 
- Integrated LinkedIn profiling so organizers can quickly vet candidates.
![Register Page](outputs/register/Screenshot%202026-04-14%20201806.png)
![Login Page](outputs/login/Screenshot%202026-04-14%20201659.png)
![User Profile](outputs/myprofile/Screenshot%202026-04-14%20201918.png)

### 2. Homepage & Dashboard
After logging in, users are directed to the dashboard where they can access event details. 
![Home](outputs/home/Screenshot%202026-04-14%20200759.png)

### 3. Location-Based Search & Discovery Module
Uses the OpenStreetMap API. The system filters events within a 50 km radius, helping users pinpoint nearby hackathons easily and efficiently.
![Hackathons Map Search](outputs/hackathons/Screenshot%202026-04-14%20201157.png)

### 4. Admin Management (Event Creation)
Administrators can create hackathons, embed exact location coordinates on the map, list prerequisites, and specify dates.
![Admin Dashboard](outputs/admin_home/Screenshot%2026-04-14%20202521.png)
*(Note: Refer to actual images for admin creation page)*

### 5. Participation Request Module
Once a user applies for a hackathon, their request goes directly to the Admin. The Admin reviews the profile (and LinkedIn) and issues an Accept/Reject.
![Admin Requests](outputs/admin_messages/Screenshot%202026-04-14%20202636.png)
![User Notification](outputs/user_notification/Screenshot%202026-04-14%20202726.png)

### 6. Team Matchmaking / Finder
Users have the ability to seek teammates within the platform.
![Find Teammates](outputs/findteammate/Screenshot%202026-04-14%20202048.png)
![Teams Panel](outputs/teams/Screenshot%202026-04-14%202140.png)

### 7. Communication System
Built-in emailing / messaging interface for quick communication between admins and users.
![Contact Interface](outputs/contact/Screenshot%202026-04-14%20202253.png)
![Mailing](outputs/mail_messages/Screenshot%202026-04-14%20203004.png)
![About Application](outputs/about/Screenshot%202026-04-14%20201347.png)

---

## Objectives

1. **Centralize Hackathon Data:** Stop relying on fragmented communication channels by bringing organizers and participants into one platform.
2. **Location Awareness:** Enable local talent discovery by making events searchable by an exact 50 km radius.
3. **End-to-End Tracking:** Track user applications and status notifications.

---

## Development Team
- **Vedant**
- **Archit**

---

## System Requirements
To run this project locally, ensure you have:
- PHP (v7.4 or newer recommended)
- MySQL Server
- XAMPP / WAMP / MAMP stack
- A web browser (Chrome, Firefox, Safari)

## Installation Guide
1. **Clone the repository.**
2. **Set up Local Server**: Move the project folder to `htdocs` (if using XAMPP).
3. **Database Import**: Create a new database in phpMyAdmin and import the `.sql` files from the `sql/` directory.
4. **Update config**: Verify `includes/db_connect.php` has your proper MySQL username/password credentials.
5. **Run the Project**: Open `http://localhost/WP-class/` in your browser.

---

## Conclusion
This Web Application provides a practical and efficient solution to the problem of hackathon visibility and accessibility. By combining modern web technologies, it acts as a bridge between individuals seeking opportunities and those creating them. 
