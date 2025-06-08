
## **Guidelines for the Generative IDE (Trae) - Conversational Task Manager Project**

**Purpose:** This document defines the rules and patterns that the Generative IDE "Trae" must follow during the development of the **Conversational Email Task Manager** project. The goal is to ensure code consistency, adherence to the planned architecture, and the implementation of best practices for Laravel and Pest.

### **1. General Principles**

1.  **Adherence to the Plan:** The primary source of truth is the **Project Document**. All features, models, and logic must follow what was specified therein. Do not introduce unsolicited features.
2.  **Simplicity and Focus:** The objective is to create a robust application for the Postmark challenge. Prioritize the clean implementation of the defined functional requirements.
3.  **Clean Code:** Generate clear, readable, and well-commented code, especially in complex logic sections like email parsing.

### **2. Architecture and Code Patterns**

1.  **The Webhook Controller is an Orchestrator:** The `WebhookController` must have a single responsibility:
    * Receive and validate the request from the Postmark webhook.
    * Delegate business logic to service classes.
    * Return a `200` HTTP response to Postmark.
    * It must **NOT** contain complex command parsing logic or business rules.

2.  **Business Logic in Service Classes:** All email command processing logic **must** be encapsulated in the `App\Services\CommandParser` service class. The controller should instantiate and call this class.

3.  **Use of Mailables for Outbound Emails:** Every transactional email sent by the application **must** use a dedicated `Mailable` class. For the task creation confirmation, use `App\Mail\TaskCreatedConfirmation`.

4.  **Views (Blade) are for Presentation:** Blade views must not contain business logic. Use Blade components (like `app/View/Components/TaskList.php`) to reuse interface elements and pass data through them.

### **3. Core Business Logic**

1.  **Mandatory Differentiation: Creation vs. Update:** This is the most critical rule. Upon receiving a payload in the webhook, the first action **must** be to check if it is a new email or a reply.
    * **Criterion:** Analyze the email's `Subject`. If it matches the pattern `Re: [TASK-XXX]`, where `XXX` is a number, treat it as an **update**.
    * Otherwise, treat it as the **creation** of a new task.

2.  **Task ID Extraction:** For updates, the task ID **must** be extracted from the subject using a regular expression (regex) to isolate the number inside `[TASK-...]`.

3.  **Parsing Delegation:** The email's `TextBody` from an update **must** be passed to the `CommandParser` service for processing. Do not implement parsing logic directly in the controller.

4.  **Respect Default Values:** When creating a new task, the following default values **must** be applied:
    * `priority`: 'medium'
    * `status`: 'open'

### **4. Database Interaction (Eloquent)**

1.  **Always via Eloquent:** All database interactions **must** be performed through Eloquent models. Use `Task::create()`, `$task->save()`, `$task->update()`. Avoid the Query Builder (`DB::table`) or raw queries.

2.  **Respect the Schema:** The `Task` model must exactly reflect the migration's structure, including the `status` and `postmark_message_id` fields.

3.  **Security in Mass Assignment:** The `Task` model **must** have the `$fillable` property correctly defined to protect against mass assignment vulnerabilities.

### **5. Testing with Pest**

1.  **Comprehensive Coverage:** Every new feature or piece of business logic **must** be accompanied by tests.
2.  **Clear Distinction between Unit and Feature Tests:**
    * **Unit Tests (`tests/Unit`):** Use for testing isolated classes, such as the `CommandParser`. Test each command (`#priority`, `#complete`) and edge cases (emails without commands, multiple commands).
    * **Feature Tests (`tests/Feature`):** Use for testing the application's full flow. Simulate an HTTP request to the webhook route and verify the results.

3.  **Mandatory Use of Fakes:**
    * In feature tests that trigger emails, **always** use `Mail::fake()`.
    * Use `Mail::assertSent()` to verify that the correct `Mailable` was sent to the correct recipient.
    * Use `Mail::assertNotSent()` to ensure that unwanted emails were not dispatched.

4.  **Database Assertions:** Verify the application's state using `assertDatabaseHas` to confirm a record was correctly created/updated and `assertDatabaseMissing` for the opposite.

### **6. Security**

1.  **Sender Validation on Update:** When processing a task update, it is **mandatory** to verify that the `From` email of the incoming webhook matches the `from_email` stored for the original task. If it does not match, the operation must be aborted.
2.  **Input Validation:** Always validate data received from the Postmark webhook before processing it to prevent errors and attacks.
