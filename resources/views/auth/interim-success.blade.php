<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Autenticado</title>
</head>
<body style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif;">
    <p>Autenticado com sucesso! Atualizando...</p>

    <script>
        // Dispara o evento para a página mãe que hospeda o iframe!
        window.parent.postMessage({ type: 'heartbeat-auth-success' }, '*');
    </script>
</body>
</html>
