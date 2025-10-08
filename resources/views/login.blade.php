<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Login</h2>
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first('login') }}</div>
        @endif
        {{ route(
            @csrf
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuário</label>
                <input type="text" class="form-control" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>