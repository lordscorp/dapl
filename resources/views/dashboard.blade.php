<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="../resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="../resources/css/custom.css">
</head>

<body>
    <div id="logout">
        <span id="nome-usuario" style="opacity: 0;">Nome: {{ $nome }}</span><span id="rf-usuario" class="mx-2">{{ $rf }}</span>
        <a href="logout.php"><button class="btn btn-danger btn-sm float-right">Sair</button></a>
    </div>
    <div id="app">
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="../resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>DAPL - Dados Abertos de Processos de Licenciamento</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4">
            <div class="card-header">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="validacao">Validação</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tutorial">Tutorial</a>
                    </li>
                </ul>
            </div>
            <div class="card-body" v-show="isCarregando">
                <h2 class="text-center">@{{msgStatus}}</h2>
                <div class="d-flex justify-content-center align-items-center"
                    v-if="msgStatus = 'Carregando...'"
                    style="height: 10vh;">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                </div>
            </div>
            <div class="card-body" v-show="!isCarregando">
                <div class="row">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Processos HIS/HMP 2014 a 2019</div>
                            <div class="card-body big-numbers btn-outline-dark text-center">@{{totalHisHmp}}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Em validação</div>
                            <div class="card-body big-numbers btn-outline-info text-center">@{{totalValidando}}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Pendentes</div>
                            <div class="card-body big-numbers btn-outline-danger text-center">@{{totalPendente}}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Validados (clique para exportar)</div>
                            <a href="api/exportarValidados" title="Clique para exportar a planilha" style="text-decoration: none !important;">
                                <div class="card-body big-numbers btn-outline-success text-center">@{{totalValidado}}</div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col">
                        <div class="progress" style="height: 50px;">
                            <div class="progress-bar" role="progressbar" :style="txtLargura(percentualConclusaoHisHmp)" aria-valuenow="percentualConclusaoHisHmp" aria-valuemin="0" aria-valuemax="100">@{{percentualConclusaoHisHmp}} %</div>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col">
                        <div class="card">
                            <div class="card-header">Validações</div>
                            <div class="card-body">
                                <table class="table">
                                    <tr v-for="(total, rf) in validadores">
                                        <td>@{{rf}}</td>
                                        <td>@{{total}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-warning" v-show="false" @click="carregarDadosDashboard(true)">Exemplo de exibição (dados fictícios)</button>
            </div>
        </div>
    </div>
</body>
<script src="../resources/js/bootstrap.bundle.min.js"></script>
<script src="../resources/js/vue.global.js"></script>
<script>
    const {
        createApp,
        reactive,
        ref
    } = Vue

    createApp({
        data() {
            return {
                isCarregando: false,
                msgStatus: "Carregando...",
                totalHisHmp: 0,
                totalValidando: 0,
                totalPendente: 0,
                totalValidado: 0,
                validadores: [],
            }
        },
        methods: {
            async carregarDadosDashboard(mock = false) {
                try {
                    let urlDadosDashboard = 'api/dadosDashboard';
                    let urlDadosDashboardMock = 'api/mockDadosDashboard';

                    this.isCarregando = true;
                    // const response = await fetch(`api/dadosDashboard`);
                    const response = await fetch(mock ? urlDadosDashboardMock : urlDadosDashboard);
                    if (!response.ok) throw new Error('Erro ao carregar dados');

                    const data = await response.json();
                    console.log('DATA', data)
                    this.isCarregando = false;

                    this.totalHisHmp = data.totalHisHmp;
                    this.totalValidando = data.totalValidando;
                    this.totalPendente = data.totalPendente;
                    this.totalValidado = data.totalValidado;
                    this.validadores = data.validadores;

                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                }
            },
            txtLargura(numero) {
                return `width: ${numero}%;`
            }
        },
        mounted() {
            this.carregarDadosDashboard();
        },
        computed: {
            percentualConclusaoHisHmp() {
                if (this.totalHisHmp === 0) return 0;
                return ((this.totalValidado / this.totalHisHmp) * 100).toFixed(2);
            }
        }
    }).mount('#app')
</script>

</html>