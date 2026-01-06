<?php
// Inicia a sessão
session_start();

// Verifica se o usuário já está logado. Se sim, redireciona à página do Painel
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

if(isset($_GET["m"])){
    echo "<script>window.alert('".$_GET["m"]."');</script>";
}

// Define as variáveis e as inicializa vazias
$usuario = $senha = "";
$username_err = $password_err = "";
 
// Processa os dados do formulário ao submeter
if($_SERVER["REQUEST_METHOD"] == "POST"){ 
    // Verifica se o e-mail foi preenchido
    if(empty(trim($_POST["usuario"]))){
        $username_err = "Digite o login.";
    } else{
        $usuario = trim($_POST["usuario"]);
    }
    
    // Verifica se a senha foi preenchida
    if(empty(trim($_POST["senha"]))){
        $password_err = "Por favor, digite a senha.";
    } else{
        $senha = trim($_POST["senha"]);
    }

    // Valida login
    // SCRIPT LDAP
    $server = "ldap://10.10.65.242";
    $ID_Usuario=mb_strtolower($_POST['usuario'],'UTF-8');
    $user = $_POST['usuario']."@rede.sp";
    $psw = $_POST['senha'];
    $inicial = $_POST['usuario'];
    $dn = "DC=rede,DC=sp";

    $search = "samaccountname=".$_POST['usuario'];  

    $ds=ldap_connect($server);
    $r=ldap_bind($ds, $user , $psw); 
    $sr=ldap_search($ds, $dn, $search);
    $data = ldap_get_entries($ds, $sr); 

    // session_start();
    if($data["count"]==0) {
        unset ($_SESSION['IDUsuario']);
        unset ($_SESSION['nomeUsuario']);
        unset ($_SESSION['emailUsuario']);
        unset ($_SESSION['setorFiscal']);
        unset ($_SESSION['divisaoFiscal']);
        header('location:login.php?m="Falha no login - Verifique seu usuario e senha."');
    }
    else {
        for ($i=0; $i<$data["count"]; $i++) {
            $nomefr = utf8_encode($data[$i]["givenname"][0]) . " " . utf8_encode($data[$i]["sn"][0]);
            $emailfr = mb_strtolower($data[$i]["mail"][0]);

            $_SESSION['usrData'] = $data[$i];
            $_SESSION['IDUsuario'] = $inicial;
            $_SESSION['nomeUsuario'] = $nomefr;
            $_SESSION['emailUsuario'] = $emailfr;
            $_SESSION['setorFiscal'] = '';
            $_SESSION['divisaoFiscal'] = '';
            
            // Verifica se usuário está cadastrado na lista de editores
            /*
            require_once "config.php";
            if (!mysqli_set_charset($link, "utf8")) {
                printf("Erro ao definir charset: %s<br>", mysqli_error($link));
                exit();
            }
            $sqlQuery = "SELECT * FROM fiscais WHERE `rf`='".strtolower($inicial)."';";
            mysqli_query($link, $sqlQuery);
            $retornoQuery = $link->query($sqlQuery);
            $servidor = [];
            if($retornoQuery->num_rows > 0){
                while ($row = $retornoQuery->fetch_assoc()) {
                    $_SESSION['setorFiscal'] = $row['setor'];
                    $_SESSION['divisaoFiscal'] = $row['divisao'];     
                }
            }
            $link->close();
            
            // Encerra cadastros
            
            if($_SESSION['setorFiscal'] == ''){
                header('location:login.php?m="Período para cadastro de bens encerrado. Acesso restrito a pontos focais."');
                return;
            }            
            */
            $_SESSION["loggedin"] = true;
            header('location:index.php');
        }
    }
    // SCRIPT LDAP
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DAPL - Dados Abertos de Processos de Licenciamento de São Paulo</title>
    <!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="../resources/css/bootstrap.min.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper" style="margin: 0 auto;">
        <div class="row mb-4">
            <div class="col text-center">
                <img src="../resources/img/logo_prefeitura.png" alt="Cidade de São Paulo" width="200">
            </div>
        </div>
        
        <br>
        <h1 style="font-size: 1.5rem;">DAPL - Dados Abertos de Processos de Licenciamento</h1>
        <hr>
        <p>Digite seu login e senha da rede</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Login</label>
                <input type="text" name="usuario" placeholder="d000000" class="form-control" value="<?php echo $usuario; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Senha</label>
                <input type="password" name="senha" autocomplete="current-password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Entrar">                
            </div>            
        </form>        
    </div>
</body>
</html>

