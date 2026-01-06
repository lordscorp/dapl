<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="resources/css/custom.css">
</head>

<body>
    <div id="app">
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>DAPL - Dados Abertos de Processos de Licenciamento</h1>
            </div>
        </div>

        <div class="modal fade" id="imagemModal" tabindex="-1" aria-hidden="true" ref="modal">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-dark">
                    <div class="modal-body p-0 text-center">
                        <img :src="imagemSelecionada">
                    </div>
                </div>
            </div>
        </div>

        <div class="card w-75 mx-auto mt-4">
            <div class="card-header">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="validacao">Validação</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page">Tutorial</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="outorga">Outorga</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="row border my-4" v-for="item in etapasTutorial">
                    <div class="col d-flex align-items-center justify-content-center">
                        <p style="font-size: large;" class="my-4">@{{item.texto}}</p>
                    </div>
                    <div class="col">
                        <img :src="urlImgTutorial(item.etapa)" @click="abrirModal(urlImgTutorial(item.etapa))" class="mw-100" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="resources/js/bootstrap.bundle.min.js"></script>
<script src="resources/js/vue.global.js"></script>
<script>
    const {
        createApp,
        reactive,
        ref
    } = Vue

    createApp({
        data() {
            return {
                imagemSelecionada: null,
                etapasTutorial: [{
                        etapa: 1,
                        texto: "Digite seu usuário e senha de rede"
                    },
                    {
                        etapa: 2,
                        texto: "Clique em Validação"
                    },
                    {
                        etapa: 3,
                        texto: "O sistema irá tentar preencher a maioria dos campos baseado no texto do documento"
                    },
                    {
                        etapa: 4,
                        texto: "O sistema pode interpretar algum valor incorretamente. Por isso, verifique cada campo e preencha o valor correto"
                    },
                    {
                        etapa: 5,
                        texto: "Preencha as unidades por categoria de uso. Se houver mais de um tipo, clique no botão [ + ] para acrescentar outras categorias"
                    },
                    {
                        etapa: 6,
                        texto: "Selecione o tipo e preencha o valor"
                    },
                    {
                        etapa: 7,
                        texto: "Confirme os valores dos demais campos e preencha com base nas informações do CERTIFICADO DE CONCLUSÃO. Se uma ou mais informações não constarem no CERTIFICADO DE CONCLUSÃO, procure no Doc Relacionado (geralmente é o ALVARÁ DE EXECUÇÃO, apostilamento ou modificativo)"
                    },
                    {
                        etapa: 8,
                        texto: "Após verificar todas as informações, clique em VALIDAR"
                    },
                    {
                        etapa: 9,
                        texto: "Deve aparecer uma tela confirmando que foi Validado com sucesso. Em caso de erro, entre em contato com Renan Moreira Gomes (via Teams)"
                    },
                ]
            }
        },
        methods: {
            abrirModal(url) {
                this.imagemSelecionada = url;
                const modal = new bootstrap.Modal(this.$refs.modal);
                modal.show();
            },
            urlImgTutorial(num) {
                let prefixo = "resources/img/tutorial/";
                if (num < 10) {
                    prefixo += "0";
                }
                return `${prefixo}${num}.png`;
            }
        }
    }).mount('#app');
</script>

</html>