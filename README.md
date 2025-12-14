# TalaTrivia  🏷️ [0.0.1]

This is a prototype of a complete API and a web frontend application, designed for users to register, play, and answer trivia questions. The platform includes a management area for administrators and a participation area for players.
It works with SQLITE database, Lumen PHP and VueJS.

It is part of a challenge required by "Talana". .


## 📦 Installation

**Docker Compose Container:**

Dependencies: Docker Desktop or similar (for production), SQLite, Composer and PHP 8.2 or higher, Node 10.19 (for development).

- Download the complete directory.

- With Docker installed run the following command (inside the general directory):
    ```bash
    docker-compose up -d --build
    ```
- Access http://localhost:8083/ (Frontend Aplication)

- Access http://localhost:8082/api/ (Backend Aplication) for API access and testing purposes.


## 💻 Usage

When you log in to the application at **http://localhost:8083/**, you can access the system using two different types of users:

### Login Credentials

#### 👨‍💼 Administrator User
- **Email:** admin@trivia.com  
- **Password:** admin123  

#### 🎮 Player Users
There are two available player accounts:

**Player One**
- **Email:** player1@trivia.com  
- **Password:** player123  

**Player Two**
- **Email:** player2@trivia.com  
- **Password:** player123  


## ☝ Assumptions and Conventions

- A default **administrator user** and **two player users** are created automatically.
- Initial **sample data** is preloaded, including trivias, questions, answer options, roles, and levels.
- To ensure proper initial operation and compliance with system requirements, the deletion of certain preloaded records (such as levels, roles, and the initial administrator user) is restricted.
- Any user can register and login to the application.
- Only the administrator can assign or unassign trivias to users.
- Only the administrator can manage trivias, users, roles, and levels.

## 🚀 Future Improvements

- Implement time limits (timers) for individual questions and/or complete trivia sessions (inside Within the Trivia and Questions API section)
- Improve error messages, validations, and exception handling by providing them in Spanish.
- Enhance UI components and apply full responsive design.
- Add unit tests for actions and increase feature test coverage for all API endpoints.
- Allow logged-in users to update their personal information and change their passwords.

## 🛠️ Architecture and Technical Decisions

- A **Conventional Commits–based** approach was applied for commit messages (https://www.conventionalcommits.org/en/v1.0.0/).
- The API is developed using a typical **MVC architecture** in **Lumen**, with the particular approach of separating CRUD logic into dedicated **application actions** (`app/Actions/{Entity}`), which are injected into controller methods. This helps keep controllers as clean and lightweight as possible.
- Most request validations are handled through **FormRequest** classes, further reducing controller complexity and improving code maintainability.
- Database creation, models, and relationships follow standard **Laravel/Lumen conventions**.
- Initial test cases were created for **authentication (login)** and **user management (admin)**.
- **Vue.js 3** is used to build the view components and to interact with the API.
- **Bootstrap 5** is used for UI styling and layout.
- View components and shared routing logic are clearly separated, including reusable elements such as the **Navbar (menu)**.

## 🧪 Teststing

- The tests are written in PHPunit format
- Run the following command (inside the backend directory):

    ```bash
    vendor/bin/phpunit --testdox --colors=always
    ```
**Test list**

User Controller tests (N2N or Feature) were written (tests\Feature\User\UserAdminTest.php)

- Admin can list all users
- Player cannot list users
- Unauthenticated user cannot list users
- Admin can view a specific user
- Admin cannot view a non-existent user
- Player cannot view user details
- Admin can create a new user
- Admin can create a user without assigning a role
- User creation validates required fields
- User creation validates email format
- User creation validates unique email
- User creation validates minimum password length
- User creation validates minimum name length
- User creation validates existing role
- Player cannot create users
- Admin can update user basic information
- Admin can update user password
- Admin can update user role
- Admin can partially update a user
- User update validates unique email
- User update allows keeping the same email
- User update validates minimum password length
- User update validates existing role
- Admin cannot update a non-existent user
- Player cannot update users
- Admin can delete a user
- Admin cannot delete a non-existent user
- Player cannot delete users
- Unauthenticated user cannot delete users
- User listing returns multiple users
- Admin can create a user with all valid fields

Auth Controller tests for Login (N2N or Feature) were written (tests\Feature\Auth\LoginTest.php)

- User can log in with valid credentials
- User cannot log in with invalid credentials
- Login requires email
- Login requires password


## 📘 API Documentation Summary

The TalaTrivia API uses the following base URL:

- **Base URL:** `http://localhost:8082/api/` (or your environment-specific URL)
- **Authentication:** Most protected routes require a **Bearer Token**

See the TalaTriviaAPI.json file (Postman collection) in the project root directory so you can import it into Postman.
---

### 🔐 Authentication

| Method | Endpoint        | Description                               | Permissions      |
|------|-----------------|-------------------------------------------|------------------|
| POST | `/login`        | Log in and obtain a JWT token              | Public           |
| POST | `/register`     | Create a new user (player by default)      | Public           |
| POST | `/logout`       | Invalidate the current session token       | Logged-in User   |
| GET  | `/me`           | Get authenticated user information         | Logged-in User   |

---

### 👥 Users

| Method | Endpoint        | Description                    | Permissions |
|------|-----------------|--------------------------------|-------------|
| GET  | `/users`        | List all users                 | Admin Only |
| POST | `/users`        | Create a new user              | Admin Only |
| PUT  | `/users/{id}`   | Update an existing user        | Admin Only |
| DELETE | `/users/{id}` | Delete a user                  | Admin Only |

---

### 🧑‍💼 Roles

| Method | Endpoint        | Description                    | Permissions |
|------|-----------------|--------------------------------|-------------|
| GET  | `/roles`        | List all roles                 | Admin Only |
| POST | `/roles`        | Create a new role              | Admin Only |
| PUT  | `/roles/{id}`   | Update an existing role        | Admin Only |
| DELETE | `/roles/{id}` | Delete a role                  | Admin Only |

---

### 🎚 Levels

| Method | Endpoint        | Description                    | Permissions |
|------|-----------------|--------------------------------|-------------|
| GET  | `/levels`       | List all levels                | Admin Only |
| POST | `/levels`       | Create a new level             | Admin Only |
| PUT  | `/levels/{id}`  | Update an existing level       | Admin Only |
| DELETE | `/levels/{id}`| Delete a level                 | Admin Only |

---

### 🧠 Trivias

| Method | Endpoint                    | Description                        | Permissions |
|------|-----------------------------|------------------------------------|-------------|
| GET  | `/trivias`                  | List all trivias                   | Admin Only |
| POST | `/trivias`                  | Create a new trivia                | Admin Only |
| PUT  | `/trivias/{id}`             | Update a trivia                    | Admin Only |
| POST | `/trivias/{id}/assign`      | Assign users to a trivia           | Admin Only |
| POST | `/trivias/{id}/unassign`    | Unassign users from a trivia       | Admin Only |

---

### ❓ Questions

| Method | Endpoint                                   | Description                               | Permissions |
|------|--------------------------------------------|-------------------------------------------|-------------|
| GET  | `/trivias/{trivia_id}/questions`           | List questions for a trivia               | Admin Only |
| POST | `/trivias/{id}/questions`                  | Store a single question                   | Admin Only |
| POST | `/trivias/{id}/questions_bulk`             | Store multiple questions in bulk          | Admin Only |
| PUT  | `/questions/{id}`                          | Update a question and its options         | Admin Only |
| DELETE | `/questions/{id}`                        | Delete a question                         | Admin Only |

---

### 🎮 Game Play

| Method | Endpoint                         | Description                                              | Permissions      |
|------|----------------------------------|----------------------------------------------------------|------------------|
| GET  | `/assigned_trivias`              | List trivias assigned to the logged-in user              | Logged-in User   |
| GET  | `/trivias/{id}/get_full`         | Start or resume a trivia (full questions and options)    | Logged-in User   |
| POST | `/questions/{id}/answer`         | Submit an answer to a question                           | Logged-in User   |
| POST | `/trivias/{id}/answer_all`       | Submit multiple answers (bulk)                           | Logged-in User   |
| GET  | `/trivias/{id}/my_answers`       | View user's answers and score for a trivia               | Logged-in User   |
| GET  | `/trivias/{id}/ranking`          | View ranking for a specific trivia                       | Logged-in User   |
| GET  | `/all_trivias_ranking`           | View global ranking across all players                   | Logged-in User   |


## 👥 Author

Abner Galvez C., using Lumen 10 , PHP 8.2, VueJS 3.5

## Project status

Completed, awaiting review.