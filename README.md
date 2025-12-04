## Установка

1. клонируйте репозиторий
2. composer install
3. php artisan migrate
4. php artisan storage:link
5. php artisan db:seed
6. php artisan queue:work

## Роуты и правила использования

**GET /api/tasks** - получение списка задач с возможностью фильтрации по статусу, исполнителю и дате завершения.

Так например GET /api/tasks вернет все доступные задачи.
Запрос GET /api/tasks?status=planned&user_id=1&end_date_from=2025-12-20&end_date_to=2025-12-20 вернет список задач со статусом "planned", исполнителем с id=1, и дадатой завершения 20.12.25.

Доступные для фильтрации параметры: status (статус задачи, должен быть один из: planned, in_progress, done); user_id (id пользователя исполнителя); end_date_from (дата завершения от); end_date_to (дата завершения до);

Пример ответа (json):
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "task тест 4",
            "description": "descr kdfjsdfs dkfdjgs ddkfgj",
            "status": "planned",
            "end_date": "2025-12-20T00:00:00.000000Z",
            "user_id": 1,
            "created_at": "2025-12-20T15:10:52.000000Z",
            "updated_at": "2025-12-20T15:10:52.000000Z",
            "user": {
                "id": 1,
                "name": "Test adm",
                "email": "example@ex.ru",
                "email_verified_at": "2025-12-20T10:10:35.000000Z",
                "created_at": "2025-12-20T10:10:35.000000Z",
                "updated_at": "2025-12-20T10:10:35.000000Z"
            }
        }
    ]
}

**POST /api/tasks** - создание новой задачи. Обязательные параметры: name, description, status. Опционально: end_date, image (file).
Пример запроса (form-data):
data = '{
    "name":"task name",
    "description":"descr task",
    "status":"planned",
    "end_date":"2025-12-10"
}'
image = 'attached file (jpeg,png,jpg,gif)'

Пример запроса без файла (json):
{
    "name":"task name",
    "description":"descr task",
    "status":"planned",
    "end_date":"2025-12-10"
}

**GET /api/tasks/{id}** - получение информации о задаче.
Пример ответа:
{
    "success": true,
    "data": {
        "id": 1,
        "name": "task name",
        "description": "descr task",
        "status": "planned",
        "end_date": null,
        "user_id": 1,
        "created_at": "2025-12-04T10:20:08.000000Z",
        "updated_at": "2025-12-04T10:20:08.000000Z",
        "user": {
            "id": 1,
            "name": "Test adm",
            "email": "example@ex.ru",
            "email_verified_at": "2025-12-04T10:10:35.000000Z",
            "created_at": "2025-12-04T10:10:35.000000Z",
            "updated_at": "2025-12-04T10:10:35.000000Z"
        }
    }
}


**PUT /api/tasks/{id}** - обновление данных задачи. Передаются параметры только те, которые должны быть обновленны. Передаются в формате json или form-data если с изображением, как в примере создания задачи POST /api/tasks.

**DELETE /api/tasks/{id}** - удаление задачи.

**POST /api/login** - аутификация пользователя.
Пример запроса (json):
{
    "email":"test@example.com",
    "password":"123"
}

Пример ответа:
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Test adm",
        "email": "test@example.com",
        "email_verified_at": "2025-12-04T10:10:35.000000Z",
        "created_at": "2025-12-04T10:10:35.000000Z",
        "updated_at": "2025-12-04T10:10:35.000000Z"
    },
    "access_token": "14|F2NjW59UcGBqTyan94lALf9HVzw25mWkz2CaYIDU7319f4dd",
    "token_type": "Bearer"
}

**POST /api/logout** - выйти из аккаунта (анулирование текущего токена).


## Важно

** Все роуты, кроме /api/login требуют аутификации (передачи в header токена Bearer xxxxx)
** При создании новой задачи, реализовано оповещение на почту пользователя создавшего задачу.