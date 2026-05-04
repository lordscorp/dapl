<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="resources/css/custom.css">
    <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@3.0.0-beta.2/dist/vue-multiselect.css">

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
        <div class="card w-90 mx-auto mt-4">
            @include('partials.header', ['active' => 'businessintelligence'])

            <!-- Consulta processos -->
            <div class="card-body">
                <!-- Busca por SQL específico -->
                <div class="row">
                    <div class="col">
                        <label class="form-label" title="Setor, Quadra, Lote">SQL</label>
                        <input type="text" class="form-control" v-model="sqlBusca">
                    </div>
                    <div class="col">
                        <button class="btn btn-info btn-lg mt-3" @click="buscarSql">Buscar</button>
                    </div>
                </div>
                <!-- Busca avançada -->
                <div class="card my-4">
                    <div class="card-header">
                        <strong>Busca avançada</strong>
                    </div>

                    <div class="card-body">
                        <div class="row my-2">
                            <!-- Assuntos -->
                            <!-- <div class="col-md-3 mb-3">
                                <label for="assuntos">Assuntos</label>
                                <select
                                    id="assuntos"
                                    class="form-control"
                                    v-model="assuntos"
                                    multiple
                                    size="5">
                                    <option v-for="assunto in listaAssuntos" :key="assunto" :value="assunto">
                                        @{{ assunto }}
                                    </option>
                                </select>
                                ASSUNTOS SELECIONADOS: @{{assuntos}}
                            </div> -->
                            <div class="col-md-12">
                                <label for="assuntos">Assuntos</label>
                                <multiselect
                                    v-model="assuntos"
                                    :options="listaAssuntos"
                                    :multiple="true"
                                    :searchable="true"
                                    :close-on-select="false"
                                    placeholder="Selecione os assuntos"
                                    :select-label="'Aperte ENTER para selecionar'"
                                    :deselect-label="'Aperte ENTER para remover'"
                                    :show-labels="true"
                                    :no-result="'Nenhum resultado'" />
                            </div>

                            <!-- <multiselect
                                v-model="testes"
                                :options="listaTestes"
                                :multiple="true"
                                :searchable="true"
                                :close-on-select="false"
                                placeholder="Selecione os testes"
                                :select-label="'Aperte ENTER para selecionar'"
                                :deselect-label="'Aperte ENTER para remover'"
                                track-by="language"
                                :show-labels="false"
                                label="name" /> -->

                        </div>

                        <div class="row my-2">
                            <!-- Situações -->
                            <div class="col-md-4">
                                <label for="situacoes">Situações</label>
                                <multiselect
                                    v-model="situacoes"
                                    :options="listaSituacoes"
                                    :multiple="true"
                                    :searchable="true"
                                    :close-on-select="false"
                                    placeholder="Selecione as situações"
                                    :select-label="'Aperte ENTER para selecionar'"
                                    :deselect-label="'Aperte ENTER para remover'"
                                    :show-labels="false"
                                    :no-result="'Nenhum resultado'" />
                            </div>
                            <!-- Distritos -->
                            <div class="col-md-4">
                                <label for="distritos">Distritos</label>
                                <multiselect
                                    v-model="distritos"
                                    :options="listaDistritos"
                                    :multiple="true"
                                    :searchable="true"
                                    :close-on-select="false"
                                    placeholder="Selecione os distritos"
                                    :select-label="'Aperte ENTER para selecionar'"
                                    :deselect-label="'Aperte ENTER para remover'"
                                    :show-labels="false"
                                    :no-result="'Nenhum resultado'" />
                            </div>


                            <!-- Subprefeituras -->
                            <div class="col-md-4">
                                <label for="subprefeituras">Subprefeituras</label>
                                <multiselect
                                    v-model="subprefeituras"
                                    :options="listaSubprefeituras"
                                    :multiple="true"
                                    :searchable="true"
                                    :close-on-select="false"
                                    placeholder="Selecione as subprefeituras"
                                    :select-label="'Aperte ENTER para selecionar'"
                                    :deselect-label="'Aperte ENTER para remover'"
                                    :show-labels="false"
                                    :no-result="'Nenhum resultado'" />
                            </div>
                        </div>
                        <div class="row">
                            <!-- Data início (obrigatório) -->
                            <div class="col-md-4">
                                <label for="dataInicio">Data início</label>
                                <input
                                    type="date"
                                    id="dataInicio"
                                    class="form-control"
                                    v-model="dataInicio"
                                    min="1900-01-01"
                                    required>
                            </div>

                            <!-- Data fim -->
                            <div class="col-md-4">
                                <label for="dataFim">Data fim</label>
                                <input
                                    type="date"
                                    id="dataFim"
                                    class="form-control"
                                    v-model="dataFim">
                            </div>
                            <!-- Botão -->
                            <div class="col-md-4 d-flex align-items-end">
                                <button
                                    class="btn btn-info w-100"
                                    @click="buscarProcessos()"
                                    :disabled="!dataInicio">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="row">
                    <div class="col">
                        <label class="form-label" title="Data inicial">De</label>
                        <input type="date" name="dataInicio" id="data-inicio" min="1990-01-01">
                    </div>
                </div> -->

                <div class="row my-4" v-show="isCarregando">
                    <div class="col text-center">Carregando...</div>
                </div>
                <div class="row my-4" v-show="!isCarregando">
                    <div class="col">
                        <div
                            class="text-center m-4"
                            v-show="jaBuscou && (!processosLocalizados || processosLocalizados.length == 0)">
                            Nenhum processo localizado
                        </div>
                        <div v-show="processosLocalizados.length > 0">
                            <div class="row my-4">
                                <div class="col cols-4"></div>
                                <div class="col text-center">
                                    <h4>Foram localizados @{{processosLocalizados.length}} processos.</h4>
                                </div>
                                <div class="col cols-4">
                                    <button class="btn btn-secondary" @click="exportarXlsx">Exportar XLSX</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <table class="table mt-3">
                                        <tr>
                                            <th>Processo</th>
                                            <th>SQL</th>
                                            <th>Sistema</th>
                                            <th>Assunto</th>
                                            <th>Situação</th>
                                            <th>Data Protocolo</th>
                                            <th>Distrito</th>
                                            <th>Subprefeitura</th>
                                            <th>Interessados</th>
                                        </tr>
                                        <tr v-for="processo in processosLocalizados">
                                            <td>@{{processo.processo}}</td>
                                            <td>@{{processo.sql}}</td>
                                            <td>@{{processo.sistema}}</td>
                                            <td>@{{processo.assunto}}</td>
                                            <td>@{{processo.SituacaoAssunto}}</td>
                                            <td>@{{ formatarData(processo.dtPedidoProtocolo) }}</td>
                                            <td>@{{processo.distrito}}</td>
                                            <td>@{{processo.subprefeitura}}</td>
                                            <td>@{{processo.interessados}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dashboard B.I. -->
        </div>
    </div>
</body>
<script src="resources/js/bootstrap.bundle.min.js"></script>
<script src="resources/js/vue.global.js"></script>

<script src="https://unpkg.com/vue-multiselect"></script>
<link rel="stylesheet" href="https://unpkg.com/vue-multiselect/dist/vue-multiselect.min.css">
<!-- <script setup>
    import Multiselect from 'vue-multiselect'
    import {ref} from 'vue'
</script> -->
<script src="https://unpkg.com/vue-multiselect@3.0.0-beta.2/dist/vue-multiselect.umd.js"></script>

<script>
    const {
        createApp,
        reactive,
        ref
    } = Vue

    // import Multiselect from 'vue-multiselect'
    // import 'vue-multiselect/dist/vue-multiselect.css'


    createApp({
        components: {
            Multiselect: window['vue-multiselect'].default
        },

        data() {
            return {
                isCarregando: false,
                jaBuscou: false,
                processosLocalizados: [],
                sqlBusca: "",

                // Busca avancada
                dataInicio: '2014-01-01',
                dataFim: null,
                assuntos: [],
                situacoes: [],
                distritos: [],
                subprefeituras: [],

                listaTestes: [{
                        name: 'Vue.js',
                        language: 'Javascript'
                    },
                    {
                        name: 'Rails',
                        language: 'Ruby'
                    },
                ],
                listaAssuntos: [],
                listaSituacoes: [],
                listaDistritos: [],
                listaSubprefeituras: [],

                testes: []
            }
        },
        methods: {
            async buscarProcessos() {
                this.processosLocalizados = [];
                try {
                    const urlBuscarProcessos = 'api/bi/buscarProcessos';

                    const payload = {
                        dataInicio: this.dataInicio,
                        dataFim: this.dataFim,
                        assuntos: this.assuntos,
                        situacoes: this.situacoes,
                        distritos: this.distritos,
                        subprefeituras: this.subprefeituras,
                        gerarXlsx: false
                    };

                    this.isCarregando = true;
                    const response = await fetch(urlBuscarProcessos, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        throw new Error(`Erro HTTP ${response.status}`);
                    }


                    const lista = await response.json();
                    console.log('DADOS: ', lista)
                    this.isCarregando = false;

                    lista.sort((a, b) => new Date(b.dtPedidoProtocolo) - new Date(a.dtPedidoProtocolo));

                    this.processosLocalizados = lista;

                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    this.isCarregando = false;
                } finally {
                    this.jaBuscou = true;
                }
            },
            async buscarSql() {
                this.processosLocalizados = [];
                try {
                    if (this.sqlBusca.length < 10) {
                        window.alert("Digite um SQL válido");
                        return;
                    }
                    let urlBuscarSql = 'api/bi/buscarsql';
                    urlBuscarSql += "?sql_incra=" + this.sqlBusca;

                    this.isCarregando = true;

                    const response = await fetch(urlBuscarSql);
                    if (!response.ok) throw new Error('Erro ao carregar dados');

                    const lista = await response.json();
                    console.log('DADOS: ', lista)
                    this.isCarregando = false;

                    lista.sort((a, b) => new Date(b.dtPedidoProtocolo) - new Date(a.dtPedidoProtocolo));

                    this.processosLocalizados = lista;

                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    // window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                } finally {
                    this.jaBuscou = true;
                }
            },
            async carregarFiltros() {
                try {
                    const response = await fetch('api/bi/listarFiltros', {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Erro HTTP ${response.status}`);
                    }

                    const dados = await response.json();

                    this.listaAssuntos = dados.assuntos || [];
                    this.listaSituacoes = dados.situacoes || [];
                    this.listaDistritos = dados.distritos || [];
                    this.listaSubprefeituras = dados.subprefeituras || [];
                } catch (error) {
                    console.error('Erro ao carregar filtros', error);
                }

            },
            async exportarXlsx() {
                const urlBuscarProcessos = 'api/bi/buscarProcessos';

                const payload = {
                    dataInicio: this.dataInicio,
                    dataFim: this.dataFim,
                    assuntos: this.assuntos,
                    situacoes: this.situacoes,
                    distritos: this.distritos,
                    subprefeituras: this.subprefeituras,
                    gerarXlsx: true,
                };

                const response = await fetch(urlBuscarProcessos, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    }).then(response => response.blob())
                    .then(blob => {
                        console.log(blob.type);

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'processos.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                    })

                
            },
            exportarXlsx1() {
                const payload = {
                    assuntos: this.assuntos,
                    situacoes: this.situacoes,
                    distritos: this.distritos,
                    subprefeituras: this.subprefeituras,
                    gerarXlsx: true
                }


                fetch('/api/bi/buscarProcessos', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.blob())
                    .then(blob => {
                        console.log(blob.type);

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'processos.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                    })
            },
            formatarData(dataCrua) {
                try {
                    let retorno = new Date(dataCrua).toLocaleDateString("pt-BR");
                    return retorno;
                } catch (err) {
                    console.warn("Erro ao formatar data:", dataCrua, err);
                    return dataCrua;
                }
            },
            txtLargura(numero) {
                return `width: ${numero}%;`
            }
        },
        mounted() {
            this.carregarFiltros();
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