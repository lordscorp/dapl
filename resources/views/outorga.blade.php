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
            @include('partials.header', ['active' => 'outorga'])
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <label class="form-label">Número SEI (AD)</label>
                        <input type="text" class="form-control" v-model="numProcessoAD">
                    </div>
                    <div class="col-2">
                        <label class="form-label">Fator Social (Fs)</label>
                        <input type="text" class="form-control" v-model="fs">
                    </div>
                    <div class="col-2">
                        <label class="form-label">Paginação</label>
                        <input type="text" class="form-control" v-model="paginacao">
                    </div>
                    <div class="col">
                        <button class="btn btn-info btn-lg mt-3" @click="buscarProcessoAD">Buscar AD</button>
                    </div>
                    <div class="col" style="display: none;">
                        <button class="btn btn-info btn-lg mt-3" @click="calcularProcessosAD">Calcular AD</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label class="form-label">Número Processo (SISACOE)</label>
                        <input type="text" class="form-control" v-model="numProcessoSISACOE">
                    </div>
                    <div class="col">
                        <button class="btn btn-info btn-lg mt-3" @click="buscarProcessoSISACOE">Buscar SISACOE</button>
                    </div>
                    <div class="col" style="display: none;">
                        <button class="btn btn-info btn-lg mt-3" @click="calcularProcessosSISACOE">Calcular SISACOE</button>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col">
                        <h2>Processo Pesquisado</h2>
                        <table class="table">
                            <tr>
                                <th>Número SEI</th>
                                <th>SQL</th>
                                <th>CODLOG</th>
                                <th>Área Terreno (At)</th>
                                <th>Área Computável (Ac)</th>
                                <th>Valor m² (V)</th>
                                <th>Fator de Planejamento (Fp)</th>
                                <th>Valor Outorga Onerosa (C*(Ac-At))</th>
                            </tr>
                            <tr>
                                <td>@{{processoPesquisado.numSei}}</td>
                                <td>@{{processoPesquisado.sql}}</td>
                                <td>@{{processoPesquisado.codlog}}</td>
                                <td>@{{processoPesquisado.areaTerreno}}</td>
                                <td>@{{processoPesquisado.areaComputavel}}</td>
                                <td>@{{processoPesquisado.vm2}}</td>
                                <td>@{{processoPesquisado.fp}}</td>
                                <td>@{{processoPesquisado.valorOutorga}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row mt-4" style="opacity: 0.75">
                    <div class="col">
                        <h2>Processos referência</h2>
                        <table class="table">
                            <tr>
                                <th>Número SEI</th>
                                <th>SQL</th>
                                <th>CODLOG</th>
                                <th>Área Terreno (At)</th>
                                <th>Área Computável (Ac)</th>
                                <th>Valor m² (V)</th>
                                <th>Fator de Planejamento (Fp)</th>
                                <th>Valor Outorga Onerosa (C*(Ac-At))</th>
                            </tr>
                            <tr v-for="processoRef in processosJaCalculados">
                                <td>@{{processoRef.numSei}}</td>
                                <td>@{{processoRef.sql}}</td>
                                <td>@{{processoRef.codlog}}</td>
                                <td>@{{processoRef.areaTerreno}}</td>
                                <td>@{{processoRef.areaComputavel}}</td>
                                <td>@{{processoRef.vm2}}</td>
                                <td>@{{processoRef.fp}}</td>
                                <td>@{{processoRef.valorOutorga}}</td>
                            </tr>
                        </table>
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
                fs: 1, // Fator Social
                paginacao: 1,
                numProcessoAD: "",
                numProcessoSISACOE: "",
                processoObj: {
                    numSei: '',
                    ano: 0,
                    valorOutorga: 0,
                    areaTerreno: 0,
                    areaComputavel: 0,
                    vm2: 0,
                    fp: 0,
                    sql: '',
                    codlog: ''
                },
                processoPesquisado: {},
                processosJaCalculados: [{
                        numSei: '1020.2021/0009574-8',
                        ano: 2025,
                        valorOutorga: 159978.64,
                        areaTerreno: 380,
                        areaComputavel: 657.49,
                        vm2: 2078.16,
                        fp: 0.6,
                        sql: '055.097.0032-7',
                        codlog: '200085'
                    }
                ]
            }
        },
        methods: {
            abrirModal(url) {
                this.imagemSelecionada = url;
                const modal = new bootstrap.Modal(this.$refs.modal);
                modal.show();
            },

            async buscarProcessoAD() {
                try {
                    const response = await fetch(`/api/outorga/buscarProcessoAD?fs=${this.fs}&processo=${encodeURIComponent(this.numProcessoAD)}`);

                    if (!response.ok) {
                        throw new Error(`Erro: ${response.status}`);
                    }

                    let processoRaw = await response.json();
                    this.processoPesquisado = this.sanitizarDadosProcessoAD(processoRaw);
                    this.erro = null;
                } catch (error) {
                    this.erro = error.message;
                    console.error(error);
                    this.processoPesquisado = {};
                }
            },
            async buscarProcessoSISACOE() {
                try {
                    const response = await fetch(`/api/outorga/buscarProcessoSISACOE?fs=${this.fs}&processo=${encodeURIComponent(this.numProcessoSISACOE)}`);

                    if (!response.ok) {
                        throw new Error(`Erro: ${response.status}`);
                    }

                    let processoRaw = await response.json();
                    this.processoPesquisado = this.sanitizarDadosProcessoSISACOE(processoRaw);
                    this.erro = null;
                } catch (error) {
                    this.erro = error.message;
                    console.error(error);
                    this.processoPesquisado = {};
                }
            },
            async calcularProcessosAD() {
                try {
                    const response = await fetch(`/api/outorga/calcularProcessosAD?fs=${this.fs}&paginacao=${this.paginacao}`);

                    if (!response.ok) {
                        throw new Error(`Erro: ${response.status}`);
                    }

                    let processoRaw = await response.json();
                    this.processoPesquisado = this.sanitizarDadosProcessoAD(processoRaw);
                    this.erro = null;
                } catch (error) {
                    this.erro = error.message;
                    console.error(error);
                    this.processoPesquisado = {};
                }
            },
            async calcularProcessosSISACOE() {
                try {
                    const response = await fetch(`/api/outorga/calcularProcessosSISACOE?fs=${this.fs}`);

                    if (!response.ok) {
                        throw new Error(`Erro: ${response.status}`);
                    }

                    let processoRaw = await response.json();
                    this.processoPesquisado = this.sanitizarDadosProcessoSISACOE(processoRaw);
                    this.erro = null;
                } catch (error) {
                    this.erro = error.message;
                    console.error(error);
                    this.processoPesquisado = {};
                }
            },

            sanitizarDadosProcessoAD(processoRaw) {
                console.log("sanitizarDadosProcessoAD")
                let processo = JSON.parse(JSON.stringify(this.processoObj));
                /**
                 * numSei: '',
                    ano: 0,
                    valorOutorga: 0,
                    areaTerreno: 0,
                    areaComputavel: 0,
                    vm2: 0,
                    fp: 0,
                    sql: '',
                    codlog: ''
                 */
                processo.numSei = processoRaw.processo;
                processo.ano = processoRaw.dt_emissao ? processoRaw.dt_emissao.substring(0, 4) : processoRaw.dt_autuacao.substring(0, 4);
                processo.areaTerreno = parseFloat(processoRaw.area_do_terreno);
                processo.areaComputavel = parseFloat(processoRaw.area_edificada_computavel);
                processo.sql = processoRaw.sqls.split(',')[0];
                processo.codlog = processoRaw.codlog;
                processo.vm2 = processoRaw.valor_m2;
                processo.fp = processoRaw.fp; // fator de planejamento
                processo.valorOutorga = processoRaw.valor_outorga;
                return processo;
            },
            sanitizarDadosProcessoSISACOE(processoRaw) {
                console.log("sanitizarDadosProcessoSISACOE")
                let processo = JSON.parse(JSON.stringify(this.processoObj));
                /**
                 * numSei: '',
                    ano: 0,
                    valorOutorga: 0,
                    areaTerreno: 0,
                    areaComputavel: 0,
                    vm2: 0,
                    fp: 0,
                    sql: '',
                    codlog: ''
                 */
                processo.numSei = processoRaw.processo;
                processo.ano = processoRaw.dataUltimoRegistro ? processoRaw.dataUltimoRegistro.substring(0, 4) : processoRaw.dataPrimeiroRegistro.substring(0, 4);
                processo.areaTerreno = parseFloat(processoRaw.areaTerreno);
                processo.areaComputavel = parseFloat(processoRaw.areaComputavel);
                processo.sql = processoRaw.sqlIncra.split(';')[0];
                processo.codlog = processoRaw.codlog;
                processo.vm2 = processoRaw.valor_m2;
                processo.fp = processoRaw.fp; // fator de planejamento
                processo.valorOutorga = processoRaw.valor_outorga;
                return processo;
            }

        }
    }).mount('#app');
</script>

</html>