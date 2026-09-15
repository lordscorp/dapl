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
    <div id="logout">
        <span id="nome-usuario" style="opacity: 0;">Nome: {{ $nome }}</span><span id="rf-usuario" class="mx-2">{{ $rf }}</span>
        <a href="logout.php"><button class="btn btn-danger btn-sm float-right">Sair</button></a>
    </div>
    <div id="app">
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>DAPL - Dados Abertos de Processos de Licenciamento</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4">
            @include('partials.header', ['active' => 'dashboard'])
            <!-- TESTE -->
        <div class="card w-75 mx-auto my-4">
            <div class="card-header">
                <!-- Pesquisar SQLs impactados pela mudança de zoneamento 18.177 -->
                 Consulta de divergências de zoneamento – Leis nº 18.081/2024 e nº 18.177/2024
            </div>

            <div class="card-body">
                <div class="input-group mb-3">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Informe o SQL (Setor/Quadra/Lote)"
                        v-model="sqlPesquisa"
                        @keyup.enter="pesquisarImpactoZoneamento">

                    <button
                        class="btn btn-primary"
                        @click="pesquisarImpactoZoneamento"
                        :disabled="isPesquisandoImpacto">
                        Pesquisar
                    </button>
                </div>

                <div v-if="isPesquisandoImpacto" class="text-center my-3">
                    <div class="spinner-border text-primary"></div>
                </div>

                <div v-if="impactoZoneamento" class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th
                                    v-for="(valor, chave) in impactoZoneamento[0]"
                                    :key="chave">
                                    @{{ chave }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(registro, index) in impactoZoneamento" :key="index">
                                <td
                                    v-for="(valor, chave) in registro"
                                    :key="chave">
                                    @{{ valor }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="pesquisaRealizada && !impactoZoneamento"
                    class="alert alert-warning">
                    Nenhum registro encontrado.
                </div>
            </div>
        </div>

        <!-- FIM TESTE -->
            <!-- <div class="card-body" v-show="isCarregando">
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
                            <div class="card-header">Processos HIS/HMP 2020 a 2026</div>
                            <div class="card-body big-numbers btn-outline-dark text-center">@{{totalHisHmp}}</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Em validação</div>
                            <div class="card-body big-numbers btn-outline-info text-center">@{{Object.keys(validando).length}}</div>
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
                            <div class="card-header">Validados</div>
                            <a href="api/exportarExcelListaBlocos" title="Clique para exportar a planilha" style="text-decoration: none !important;">
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
                    <div class="col">
                        <div class="card">
                            <div class="card-header">Em validação</div>
                            <div class="card-body">
                                <table class="table">
                                    <tr v-for="validador in validando">
                                        <td>@{{validador.rfValidador}}</td>
                                        <td>@{{validador.NumeroAD}}</td>
                                        <td>@{{validador.SQL.substr(0, 12)}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-warning" v-show="false" @click="carregarDadosDashboard(true)">Exemplo de exibição (dados fictícios)</button>
            </div> -->
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
                isCarregando: false,
                isPesquisandoImpacto: false,
                pesquisaRealizada: false,
                sqlPesquisa: '',
                impactoZoneamento: null,
                msgStatus: "Carregando...",
                totalHisHmp: 0,
                totalValidando: 0,
                totalPendente: 0,
                totalValidado: 0,
                validadores: [],
                validando: [],
            }
        },
        methods: {
            async carregarDadosDashboard(mock = false) {
                try {
                    let urlDadosDashboard = 'api/dadosDashboard';

                    this.isCarregando = true;
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
                    this.validando = data.validando;

                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                }
            },
            txtLargura(numero) {
                return `width: ${numero}%;`
            },
            async pesquisarImpactoZoneamento() {
                try {
                    if (!this.sqlPesquisa) {
                        alert('Informe um SQL.');
                        return;
                    }

                    this.pesquisandoImpacto = true;
                    this.pesquisaRealizada = false;
                    this.impactoZoneamento = null;

                    const response = await fetch(
                        `api/impacto-zoneamento-rev18177?sql=${encodeURIComponent(this.sqlPesquisa)}`
                    );

                    if (response.status === 404) {
                        this.pesquisaRealizada = true;
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Falha ao consultar SQL');
                    }

                    const data = await response.json();

                    this.impactoZoneamento = data.data;
                    this.pesquisaRealizada = true;
                } catch (error) {
                    console.error(error);
                    alert('Erro ao consultar impacto de zoneamento.');
                } finally {
                    this.pesquisandoImpacto = false;
                }
            }
        },
        mounted() {
            // this.carregarDadosDashboard();
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