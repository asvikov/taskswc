<!DOCTYPE html>
<html>
<head>
    <title>Создана новая задача</title>
</head>
<body>
    <h2>Приветствую, {{ $task->user->name }}!</h2>
    
    <p>Новая задача создана и назначена вам.</p>
    
    <h3>{{ $task->name }}</h3>
    
    <p><strong>Описание:</strong> {{ $task->description }}</p>
    
    <p><strong>Статус:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
    
    @if($task->end_date)
        <p><strong>Дата завершения:</strong> {{ $task->end_date->format('F j, Y') }}</p>
    @endif
    
</body>
</html>