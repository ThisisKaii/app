# Project Management & Budgeting System

A powerful Laravel-based application for managing tasks, tracking budgets, and enabling real-time team collaboration. This system supports multiple board types (Business, Personal) and features robust Role-Based Access Control (RBAC).

## Key Features

-   **Task Management**: Organize tasks with ease using drag-and-drop Kanban boards or structured table views.
-   **Budgeting (Business Boards)**:
    -   Track estimated vs. actual expenses.
    -   Manage budget categories and expense records.
    -   Monitor approval statuses (Draft, Approved, Completed).
-   **Role-Based Access Control**:
    -   **Owner**: Full system control.
    -   **Admin**: Management of tasks, budgets, and members.
    -   **Member**: Collaboration and assigned task management.
-   **Real-Time Collaboration**: Live updates for tasks and activities powered by Livewire.
-   **Activity Logging**: Comprehensive audit trails for all system actions.
-   **Modern UI**: Sleek, dark-themed interface built with Tailwind CSS.

## Technology Stack

-   **Framework**: Laravel 10.x
-   **Frontend**: Blade Templates, Tailwind CSS
-   **Interactivity**: Livewire 3.x, Alpine.js
-   **Database**: MySQL / SQLite (configurable)

## Installation & Setup

1. **Prerequisites**: Ensure you have PHP 8.1+, Composer, and Node.js/NPM installed.
2. **Clone Repository**:
    ```bash
    git clone <repository-url>
    ```
3. **Install Dependencies**:
    ```bash
    composer install
    npm install
    ```
4. **Environment Setup**:
    - Copy `.env.example` to `.env`.
    - Configure your database credentials in `.env`.
5. **Key Generation**:
    ```bash
    php artisan key:generate
    ```
6. **Database Migration**:
    ```bash
    php artisan migrate
    ```
7. **Run Seeder**:
    ```bash
    php artisan db:seed
    ```
8. **Build Assets**:
    ```bash
    npm run dev
    # Or for production
    npm run build
    ```
9. **Run Server**:
    ```bash
    php artisan serve
    ```

## Documentation & Support

For detailed system documentation, please refer to the `SYSTEM_DOCUMENTATION.txt` file located in this directory.

- My [Facebook Profile](https://www.facebook.com/asingua.joshua499/)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
