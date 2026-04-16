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
        <span id="nome-usuario" style="opacity: 0;">Nome: {{ $nome }}</span><span id="rf-usuario">{{ $rf }}</span>
        <a href="logout.php"><button class="btn btn-danger btn-sm float-right">Sair</button></a>
    </div>
    <div id="app">
        <!-- ALERTAS E MENSAGENS TEMPORARIAS -->
        <div
            v-if="toast.visivel"
            class="toast-container position-fixed top-0 end-0 p-3"
            style="z-index: 999">
            <div
                :class="['toast align-items-center show fade', toast.tipoClasse]"
                role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        @{{ toast.mensagem }}
                    </div>
                </div>
            </div>
        </div>
        <!-- FIM ALERTAS -->
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>DAPL - Dados Abertos de Processos de Licenciamento</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4" id="container-principal">
            @include('partials.header', ['active' => 'validacao'])
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
            <div class="card-body" v-show="!isCarregando && !possuiConteudo">
                <h2 class="text-center">Não há processos pendentes de validação</h2>
            </div>
            <div class="card-body px-4" v-show="!isCarregando && possuiConteudo">
                <div id="fixada" style="position: sticky; top: 0; background-color: white; z-index: 10; padding: 10px; border-bottom: 1px solid #ccc;">
                    <div class="card-title">
                        <h3>@{{ objProcesso.Assunto }}</h3>
                        <h3>Processo: @{{ objProcesso.NumeroAD }} - SEI: @{{ objProcesso.NumeroSEI }}</h3>
                    </div>
                    <!-- <h4>cat identificada: @{{objProcesso.cat}}</h4> -->
                    <h4>Tipologia: @{{objProcesso.Tipologia}}</h4>
                    <div id="sqlincra" :title="objProcesso.SQL" style="overflow: auto; max-height: 3em; max-width: 30em;" class="mr-1">SQL: @{{ objProcesso.SQL}}</div>
                    <hr>
                    <!-- Link do processo -->
                    <div class="row">
                        <div class="col text-center"><a :href="processoLinkAD" target="_blank"><button class="btn btn-info">ABRIR DOCUMENTOS DO PROCESSO</button></a>&nbsp;</div>
                        <div v-if="processoVinculadoLinkAD != PREFIXO_LINK_AD" class="col text-center"><a :href="processoVinculadoLinkAD" target="_blank"><button class="btn btn-warning">ABRIR DOCUMENTOS DO PROCESSO VINCULADO</button></a></div>
                    </div>
                    <hr>
                    <div class="row g-1">
                        <!-- <div class="col-2" v-if="false">
                            <label class="form-label">Data de Emissão</label>
                            <input type="date" class="form-control" v-model="objProcesso.dtEmissao" disabled>
                        </div> -->
                        <div class="col-2">
                            <label class="form-label">Blocos</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumBlocos">
                        </div>
                        <div class="col-2">
                            <label class="form-label">Pavimentos</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumPavimentos">
                        </div>
                        <div class="col-2">
                            <label class="form-label">HIS1</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumUnidadesHIS1">
                        </div>
                        <div class="col-2">
                            <label class="form-label">HIS2</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumUnidadesHIS2">
                        </div>
                        <div class="col-2">
                            <label class="form-label">HMP</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumUnidadesHMP">
                        </div>
                        <div class="col-2">
                            <label class="form-label">R2h/R2v</label>
                            <input type="number" class="form-control" v-model="objProcesso.NumUnidadesR2hR2v">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            Total de unidades: @{{objProcesso.NumUnidadesResidenciais}}
                        </div>
                        <div class="col text-right">
                            <label class="form-label">Planta apresenta numeração das unidades?</label>
                            <input type="checkbox" class="form-control" v-model="objProcesso.plantaExplicitaUnidades">
                        </div>
                    </div>

                    <div class="row my-4" v-for="(bloco, indiceBloco) in objProcesso.listaBlocos">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col">Bloco @{{indiceBloco + 1}}</div>
                                        <div class="col col-8 text-right">
                                            <div class="row">
                                                <label class="form-label col col-form-label">Pavimentos com unidades residenciais abaixo do térreo</label>
                                                <div class="col col-sm-2" style="min-width: 80px;"><input type="number" class="form-control" min="0" v-model="objProcesso.listaBlocos[indiceBloco].pavimentosNegativos"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="card-body">@{{bloco}}</div> -->
                                <div class="card-body">
                                    <!-- PAVIMENTO INICIO -->
                                    <div class="row border" v-for="(pavimento, indicePavimento) in objProcesso.listaBlocos[indiceBloco].listaPavimentos" :key="indicePavimento">
                                        <div class="col">
                                            <label class="form-label">
                                                <span v-if="indicePavimento - objProcesso.listaBlocos[indiceBloco].pavimentosNegativos == 0">Térreo</span>
                                                <span v-else>Pavimento @{{indicePavimento - objProcesso.listaBlocos[indiceBloco].pavimentosNegativos}}</span>
                                            </label>
                                            <div class="row g-2">
                                                <div :class="'col col-sm-2 '+unidade.cat" v-for="(unidade, indiceUnidade) in objProcesso.listaBlocos[indiceBloco].listaPavimentos[indicePavimento].listaUnidades" :key="indiceUnidade">
                                                    <label class="form-label mb-0 mt-2">Unidade @{{indiceUnidade+1}}</label>
                                                    <select class="form-select" v-model="unidade.cat">
                                                        <option disabled value="">Selecione</option>
                                                        <option v-for="opcao in opcoesUniCatUso" :key="opcao" :value="opcao">@{{ opcao }}</option>
                                                    </select>
                                                </div>
                                                <!-- <div class="col">
                                                    <input type="number" class="form-control uniCatInput" v-model="item.valor">
                                                </div> -->
                                            </div>
                                            <!-- ADICIONAR UNIDADE -->
                                            <button class="btn btn-sm btn-outline-primary m-1" @click="adicionarUnidade(indiceBloco, indicePavimento, false)" v-if="podeAdicionarUnidade">
                                                <strong>Adicionar Unidade</strong>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary m-1" @click="adicionarUnidade(indiceBloco, indicePavimento, true)" v-if="podeAdicionarUnidade">
                                                <strong>Copiar Unidade</strong>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary m-1" @click="adicionarUnidade(indiceBloco, indicePavimento, true, true)" v-if="podeAdicionarUnidade">
                                                <strong>Copiar x@{{multiplicador}} uni.</strong>
                                            </button>
                                            <!-- REMOVER UNIDADE -->
                                            <button class="btn btn-sm btn-outline-danger m-1" @click="removerUnidade(indiceBloco, indicePavimento)">
                                                <strong>Remover Unidade</strong>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- PAVIMENTO FIM -->
                                    <div class="row">
                                        <div class="col text-right">
                                            <!-- ADICIONAR Pavimento -->
                                            <button class="btn btn btn-primary m-1" @click="adicionarPavimento(indiceBloco, false)">
                                                <strong>Adicionar Pavimento</strong>
                                            </button>
                                            <button class="btn btn btn-primary m-1" @click="adicionarPavimento(indiceBloco, true)">
                                                <strong>Copiar Pavimento</strong>
                                            </button>
                                            <button class="btn btn btn-primary m-1" @click="adicionarPavimento(indiceBloco, true, true)">
                                                <strong>Copiar x@{{multiplicador}} pav.</strong>
                                            </button>
                                            <!-- REMOVER Pavimento -->
                                            <button class="btn btn btn-danger m-1" @click="removerPavimento(indiceBloco)">
                                                <strong>Remover Pavimento</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col text-center">
                            <!-- ADICIONAR Bloco -->
                            <button class="btn btn-lg btn-outline-primary m-1" @click="adicionarBloco(false)">
                                <strong>Adicionar Bloco</strong>
                            </button>
                            <button class="btn btn-lg btn-outline-primary m-1" @click="adicionarBloco(true)">
                                <strong>Copiar Bloco</strong>
                            </button>
                            <!-- REMOVER Bloco -->
                            <button class="btn btn-lg btn-outline-danger m-1" @click="removerBloco()">
                                <strong>Remover Bloco</strong>
                            </button>
                        </div>
                    </div>

                    <div class="row my-4">
                        <div class="col">
                            <button class="btn btn-success btn-lg w-100" :disabled="!podeValidar" @click="validarProcessoUnidades">Validar</button>
                        </div>
                    </div>
                    <!-- <div class="row my-4">
                        <div class="col text-right">
                            Caso já tenha preenchido tudo e, por motivo de força maior, não consiga validar, clique no botão a seguir e cole o texto no Teams (ou em bloco de notas e salve):
                            <button class="btn btn-danger" @click="botaoDoPanico">Botão do Pânico (copiar para área de transferência)</button>
                        </div>
                    </div> -->
                    <div class="row my-4">
                        <div class="col text-right">
                            Caso a planta/peça gráfica não possua nenhuma legenda explicitando o tipo de uso, tornando impossível a validação de cada unidade, clique no botão a seguir:
                            <button class="btn btn-warning" @click="adicionarProcessoAListaNegra">Adicionar processo à Lista X</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="caixa-atribuir" class="card" v-if="podeAtribuirProcessos">
            <div class="card-header">
                Atribuir processo
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <label class="form-label">RF (d123456)</label>
                        <input type="text" class="form-control" v-model="rfAtribuido">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label class="form-label">Número AD</label>
                        <input type="text" class="form-control" v-model="numeroAD">
                    </div>
                </div>
                <div class="row my-2">
                    <div class="col">
                        <button class="btn btn-info" @click="atribuirProcesso">Atribuir processo AD</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="caixa-multiplicador" class="card">
            <div class="card-header">
                Multiplicador
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <input type="number" class="form-control" min="1" v-model="multiplicador">
                    </div>
                </div>
            </div>
        </div>
        <div id="contador" class="card">
            <div class="card-header">
                Unidades Restantes
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col HIS1">HIS1: </div>
                    <div class="col HIS1 text-right">@{{objProcesso.NumUnidadesHIS1 - unidadesAtribuidasHIS1}}</div>
                </div>
                <div class="row">
                    <div class="col HIS2">HIS2: </div>
                    <div class="col HIS2 text-right">@{{objProcesso.NumUnidadesHIS2 - unidadesAtribuidasHIS2}}</div>
                </div>
                <div class="row">
                    <div class="col HMP">HMP: </div>
                    <div class="col HMP text-right">@{{objProcesso.NumUnidadesHMP - unidadesAtribuidasHMP}}</div>
                </div>
                <div class="row">
                    <div class="col R2v">R2v: </div>
                    <div class="col R2v text-right">@{{objProcesso.NumUnidadesR2hR2v - unidadesAtribuidasR2hR2v}}</div>
                </div>
                <!-- <div class="row">
                    <div class="col HIS2">HIS2: 100</div>
                </div>
                <div class="row">
                    <div class="col HMP">HMP: 100</div>
                </div>
                <div class="row">
                    <div class="col R2v">R2v/R2h: 100</div>
                </div> -->
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
                // EXEMPLO LINK AD: https://www.portaldolicenciamentosp.com.br/consulta/process/view/saopaulosp/65833-26-SP-ALV/vwbhmny6
                PREFIXO_LINK_AD: 'https://www.portaldolicenciamentosp.com.br/consulta/process/view/saopaulosp/',
                SUBPREFEITURAS: ['Aricanduva', 'Butantã', 'Campo Limpo', 'Capela do Socorro', 'Casa Verde', 'Cidade Ademar', 'Cidade Tiradentes', 'Ermelino Matarazzo', 'Freguesia/Brasilândia', 'Guaianases', 'Ipiranga', 'Itaim Paulista', 'Itaquera', 'Jabaquara', 'Jaçanã/Tremembé', 'Lapa', 'MBoi Mirim', 'Mooca', 'Parelheiros', 'Penha', 'Perus/Anhaguera', 'Pinheiros', 'Pirituba/Jaraguá', 'Santana/Tucuruvi', 'Santo Amaro', 'São Mateus', 'São Miguel', 'Sapopemba', 'Sé', 'Vila Maria/Vila Guilherme', 'Vila Mariana', 'Vila Prudente'],
                avisos: [],

                toast: {
                    visivel: false,
                    mensagem: '',
                    tipoClasse: 'text-bg-success', // success | danger | warning | info
                    timeout: null
                },

                // opcoesUniCatUsoR: ['EHIS', 'EHMP', 'HIS', 'HMP', 'R1', 'R2'],
                // opcoesUniCatUsoNR: ['nRa', 'nR1', 'nR2', 'nR3', 'Ind 1a', 'Ind 1b', 'Ind 2', 'Ind 3', 'INFRA'],
                opcoesUniCatUsoR: ['HIS1', 'HIS2', 'HMP', 'R2v', 'R2h'],
                opcoesUniCatUsoNR: ['nR1', 'nR2', 'nR3'],
                opcoesUniCatUso: [],

                termosDestaque: {
                    'PAVIMENTO': 'destaque-1',
                    'ANDAR': 'destaque-1',
                    'BLOCO': 'destaque-1',
                    'PREDIO': 'destaque-1',
                    'TORRE': 'destaque-1',
                    'PRÉDIO': 'destaque-1',
                    'UNIDADES': 'destaque-1',
                    'ÁREA': 'destaque-2',
                    'AREA': 'destaque-2',
                    'OUTORGA': 'destaque-outorga',
                    'ZONEAMENTO': 'destaque-2',
                    'ANTERIOR': 'destaque-ruim',
                    'ATUAL': 'destaque-bom',
                    'LEI': 'destaque-3',
                    'DECRETO': 'destaque-3',
                    'USO DO IMÓVEL': 'destaque-3',
                    'USO DO IMOVEL': 'destaque-3',
                    'R1': 'destaque-4',
                    'R2': 'destaque-4',
                    'HMP': 'destaque-4',
                    'H.M.P': 'destaque-4',
                    'H M P': 'destaque-4',
                    'HIS': 'destaque-4',
                    'H.I.S': 'destaque-4',
                    'H I S': 'destaque-4',
                    'nRa': 'destaque-5',
                    'nR1': 'destaque-5',
                    'nR2': 'destaque-5',
                    'nR3': 'destaque-5',
                    'Ind 1a': 'destaque-5',
                    'Ind 1b': 'destaque-5',
                    'Ind 2': 'destaque-5',
                    'Ind 3': 'destaque-5',
                    'INFRA': 'destaque-5',
                    'PROPRIETARIO': 'destaque-5',
                    'PROPRIETÁRIO': 'destaque-5',
                },

                isCarregando: false,
                msgStatus: 'Carregando...',
                expandida: false,
                valoresCalc: [],
                autosaveTimeout: null,

                multiplicador: 1,

                possuiConteudo: false,

                objProcesso: {
                    // id: 1,
                    // Assunto: "Alvará de Aprovação e Execução de Edificação Nova",
                    // NumeroAD: "64581-26-SP-ALV",
                    // LinkProcessoAD: "64581-26-SP-ALV\/0cjugeah",
                    // NumeroSEI: "1020.2026\/0002100-0",
                    // Tipologia: "R2v,HIS 1,HIS 2",
                    // NumTotalUnidades: 685,
                    // DataCriacao: "2026-01-26",
                    // SQL: "11024700762",
                    // Endereco: "Rua Dr. Saul de Camargo Neves,180,Vila Constan\u00e7a",
                    // NumBlocos: 2,
                    // NumPavimentos: 11,
                    // NumUnidadesResidenciais: 685,
                    // NumUnidadesHIS: 541,
                    // NumUnidadesHIS1: 191,
                    // NumUnidadesHIS2: 350,
                    // NumUnidadesHMP: 0,
                    // NumUnidadesR2hR2v: 144
                },
                objProcessoVinculado: {},
                objBloco: {
                    numPavimentosBloco: 20,
                    maxUnidadesPorPavimento: 4,
                    listaPavimentos: [{
                            numHis1Pav: 4
                        },
                        {
                            numHis1Pav: 4
                        },
                        {
                            numHis1Pav: 4
                        },
                        // ...
                        {
                            numR2hR2vPav: 2
                        }
                    ]
                }
            }
        },
        computed: {
            estiloBotaoCalculadora() {
                let largura = this.expandida ? 250 : 100;
                return `width: ${largura}px;`;
            },
            podeAdicionarUnidade() {
                // Simplificado para sempre permitir. (alterar aqui caso necessário)
                return true;
            },
            podeAtribuirProcessos() {
                let rfAtual = document.getElementById('rf-usuario')?.textContent?.trim();
                if (rfAtual === "d851026" || rfAtual === "d912346") {
                    return true;
                }
                return false;
            },
            processoLinkAD() {
                let linkAD = this.PREFIXO_LINK_AD + this.objProcesso?.LinkProcessoAD;
                return linkAD;
            },
            processoVinculadoLinkAD() {
                let linkAD = this.PREFIXO_LINK_AD + this.objProcessoVinculado?.LinkProcessoAD;
                return linkAD;
            },

            somaTotal() {
                return this.valoresCalc.reduce((acc, val) => acc + (parseFloat(val) || 0), 0)
            },
            unidadesAtribuidasHIS1() {
                if (!this.objProcesso?.listaBlocos) return 0;

                let total = 0;

                this.objProcesso.listaBlocos.forEach(bloco => {
                    bloco.listaPavimentos?.forEach(pavimento => {
                        pavimento.listaUnidades?.forEach(unidade => {
                            if (unidade.cat === 'HIS1') {
                                total += 1;
                            }
                        });
                    });
                });

                return total;
            },
            unidadesAtribuidasHIS2() {
                if (!this.objProcesso?.listaBlocos) return 0;

                let total = 0;

                this.objProcesso.listaBlocos.forEach(bloco => {
                    bloco.listaPavimentos?.forEach(pavimento => {
                        pavimento.listaUnidades?.forEach(unidade => {
                            if (unidade.cat === 'HIS2') {
                                total += 1;
                            }
                        });
                    });
                });

                return total;
            },
            unidadesAtribuidasHMP() {
                if (!this.objProcesso?.listaBlocos) return 0;

                let total = 0;

                this.objProcesso.listaBlocos.forEach(bloco => {
                    bloco.listaPavimentos?.forEach(pavimento => {
                        pavimento.listaUnidades?.forEach(unidade => {
                            if (unidade.cat === 'HMP') {
                                total += 1;
                            }
                        });
                    });
                });

                return total;
            },
            unidadesAtribuidasR2hR2v() {
                if (!this.objProcesso?.listaBlocos) return 0;

                let total = 0;

                this.objProcesso.listaBlocos.forEach(bloco => {
                    bloco.listaPavimentos?.forEach(pavimento => {
                        pavimento.listaUnidades?.forEach(unidade => {
                            if (unidade.cat === 'R2v' || unidade.cat === 'R2h') {
                                total += 1;
                            }
                        });
                    });
                });

                return total;
            },
            podeValidar() {
                /*
                <div class="col HIS1">HIS1: </div>
                    <div class="col HIS1 text-right">@{{objProcesso.NumUnidadesHIS1 - unidadesAtribuidasHIS1}}</div>
                </div>
                <div class="row">
                    <div class="col HIS2">HIS2: </div>
                    <div class="col HIS2 text-right">@{{objProcesso.NumUnidadesHIS2 - unidadesAtribuidasHIS2}}</div>
                </div>
                <div class="row">
                    <div class="col HMP">HMP: </div>
                    <div class="col HMP text-right">@{{objProcesso.NumUnidadesHMP - unidadesAtribuidasHMP}}</div>
                </div>
                <div class="row">
                    <div class="col R2v">R2v: </div>
                    <div class="col R2v text-right">@{{objProcesso.NumUnidadesR2hR2v - unidadesAtribuidasR2hR2v}}</div>
                */
                let his1Restantes = this.objProcesso.NumUnidadesHIS1 - this.unidadesAtribuidasHIS1;
                let his2Restantes = this.objProcesso.NumUnidadesHIS2 - this.unidadesAtribuidasHIS2;
                let hmpRestantes = this.objProcesso.NumUnidadesHMP - this.unidadesAtribuidasHMP;
                let r2hr2vRestantes = this.objProcesso.NumUnidadesR2hR2v - this.unidadesAtribuidasR2hR2v;
                if (!his1Restantes && !his2Restantes & !hmpRestantes & !r2hr2vRestantes) {
                    return true;
                }

                return false;
            }
        },
        watch: {
            objProcesso: {
                deep: true,
                handler() {
                    clearTimeout(this.autosaveTimeout);
                    // Backup automatico / 'Autosave'
                    this.autosaveTimeout = setTimeout(() => {
                        try {
                            localStorage.setItem(
                                'processo_backup',
                                JSON.stringify(this.objProcesso)
                            );
                            console.log("Autosave em " + new Date(Date.now()));
                        } catch (e) {
                            console.warn('Falha ao salvar backup local', e);
                        }
                    }, 1000);
                }
            }
        },
        mounted() {
            this.carregarOptions();
            this.carregarProcesso();
        },
        methods: {
            restaurarBackupCorrespondente() {
                const backup = localStorage.getItem('processo_backup');
                if (!backup) return;

                try {
                    const objBackup = JSON.parse(backup);

                    if (
                        objBackup &&
                        objBackup.id !== undefined &&
                        objBackup.id === this.objProcesso.id
                    ) {
                        this.objProcesso = objBackup;
                        // window.alert("Dados recuperados com sucesso :)")
                        this.mostrarToast('Dados recuperados com sucesso', 'success');
                    } else {
                        localStorage.removeItem('processo_backup');
                    }
                } catch (e) {
                    localStorage.removeItem('processo_backup');
                }
            },

            async botaoDoPanico() {
                try {
                    if (!this.objProcesso) {
                        console.warn('objProcesso está vazio');
                        return;
                    }

                    const json = JSON.stringify(this.objProcesso);

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(json);
                        console.log('JSON copiado (Clipboard API)');
                        return;
                    }

                    const textarea = document.createElement('textarea');
                    textarea.value = json;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';

                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);

                    console.log('JSON copiado (fallback)');
                    window.alert("Copiado com sucesso. Cole no Bloco de Notas e salve.")
                } catch (error) {
                    console.error('Erro ao copiar JSON:', error);
                }
            },

            downloadObjetoProcesso() {
                let conteudo = '';
                let mimeType = 'application/json';
                let extensao = 'json';

                try {
                    // Gera JSON compacto (ideal para armazenamento)
                    conteudo = JSON.stringify(this.objProcesso);

                    // Validação explícita (garantia extra)
                    JSON.parse(conteudo);
                } catch (e) {
                    // Fallback para texto
                    conteudo = String(this.objProcesso);
                    mimeType = 'text/plain';
                    extensao = 'txt';
                }

                const blob = new Blob([conteudo], {
                    type: mimeType
                });
                const url = window.URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = `processo_${this.objProcesso.id}_${Date.now()}.${extensao}`;
                document.body.appendChild(a);
                a.click();

                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            },

            adicionarBloco(deveCopiar = false) {
                let objBloco = {
                    pavimentosNegativos: 0,
                    listaPavimentos: []
                }

                let listaRef = this.objProcesso.listaBlocos;

                if (deveCopiar) {
                    objBloco = JSON.parse(JSON.stringify(listaRef[listaRef.length - 1]))
                }

                listaRef.push(objBloco);
            },
            removerBloco() {
                this.objProcesso.listaBlocos.pop();
            },
            adicionarPavimento(indiceBloco, deveCopiar = false, deveUsarMultiplicador = false) {
                let objPavimento = {
                    listaUnidades: [{
                        cat: ""
                    }]
                }
                let listaRef = this.objProcesso.listaBlocos[indiceBloco].listaPavimentos;
                console.log("adicionarPavimento - listaRef", listaRef)

                if (deveCopiar) {
                    try {
                        objPavimento.listaUnidades = [];

                        for (let unidadeDoPavimento of listaRef[listaRef.length - 1].listaUnidades) {
                            objPavimento.listaUnidades.push(JSON.parse(JSON.stringify(unidadeDoPavimento)));
                        }
                    } catch (e) {
                        console.error("ERRO AO COPIAR PAVIMENTO: ", e);
                    }
                }

                let multiplicadorUsado = deveUsarMultiplicador ? this.multiplicador : 1;
                for (let i = 0; i < multiplicadorUsado; i++) {
                    listaRef.push(objPavimento);
                }
            },
            removerPavimento(indiceBloco) {
                this.objProcesso.listaBlocos[indiceBloco].listaPavimentos.pop();
            },
            adicionarUnidade(indiceBloco, indicePavimento, deveCopiar = false, deveUsarMultiplicador = false) {
                console.log("AdicionarUnidade", indiceBloco, indicePavimento, deveCopiar);
                // objProcesso.listaBlocos[indiceBloco].listaPavimentos[indicePavimento].listaUnidades
                let objUnidade = {
                    cat: null
                };
                let listaRef = this.objProcesso.listaBlocos[indiceBloco].listaPavimentos[indicePavimento].listaUnidades;
                console.log(this.objProcesso.listaBlocos);

                if (deveCopiar) {
                    try {
                        objUnidade.cat = listaRef[listaRef.length - 1].cat;
                    } catch (e) {
                        console.error("ERRO AO COPIAR UNIDADE: ", e);
                    }
                }
                let multiplicadorUsado = deveUsarMultiplicador ? this.multiplicador : 1;
                for (let i = 0; i < multiplicadorUsado; i++) {
                    listaRef.push(objUnidade);
                }
            },
            removerUnidade(indiceBloco, indicePavimento) {
                this.objProcesso.listaBlocos[indiceBloco].listaPavimentos[indicePavimento].listaUnidades.pop();
            },

            carregarOptions() {
                this.opcoesUniCatUso = this.opcoesUniCatUsoR.concat(this.opcoesUniCatUsoNR);
            },

            async adicionarProcessoAListaNegra() {

                const confirmar = window.confirm(
                    'Tem certeza que deseja adicionar o processo à lista?'
                );

                if (!confirmar) {
                    return; // usuário cancelou
                }

                const rfValidador = document.getElementById('rf-usuario')?.textContent?.trim();

                if (!rfValidador) {
                    console.warn('rfValidador não encontrado');
                    return;
                }

                try {
                    this.isCarregando = true;

                    const response = await fetch(
                        `api/adicionarProcessoAListaNegra?rfValidador=${encodeURIComponent(rfValidador)}&id=${this.objProcesso.id}`
                    );

                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }

                    const data = await response.json();

                    // opcional: feedback visual
                    window.alert('Processo adicionado à lista com sucesso.');

                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert('FALHA AO CARREGAR: ' + error);
                } finally {
                    this.isCarregando = false;
                }
            },

            async carregarProcesso() {
                const rfValidador = document.getElementById('rf-usuario')?.textContent?.trim();

                if (!rfValidador) {
                    console.warn('rfValidador não encontrado');
                    return;
                }

                try {
                    this.isCarregando = true;
                    const response = await fetch(`api/processoUnidadesAValidar?rfValidador=${encodeURIComponent(rfValidador)}`);
                    if (!response.ok) throw new Error(response);

                    const data = await response.json();

                    if (!data.objProcesso) {
                        this.possuiConteudo = false;
                        this.isCarregando = false;
                        return;
                    }

                    this.objProcesso = data.objProcesso;
                    this.objProcessoVinculado = data.objProcessoVinculado;
                    this.estimarDistribuicaoDeUnidades();
                    this.possuiConteudo = true;
                    try {
                        this.restaurarBackupCorrespondente();
                    } catch (e) {
                        console.error("Falha ao restaurar backup automatico", e);
                    }
                    this.isCarregando = false;
                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                }
            },

            atribuirAreas() {
                this.objProcesso.areaTotal = parseFloat((this.objProcesso.areaTotal || '0').replace(',', '.')) || 0;
                this.objProcesso.areaConstruida = parseFloat((this.objProcesso.areaConstruida || '0').replace(',', '.')) || 0;
                this.objProcesso.areaComputavel = parseFloat((this.objProcesso.areaComputavel || '0').replace(',', '.')) || 0;
            },

            destacarTermos(texto) {
                if (!texto) return '';

                let resultado = texto;

                for (const [palavra, classe] of Object.entries(this.termosDestaque)) {
                    const regex = new RegExp(`(${palavra})`, 'gi');
                    resultado = resultado.replace(regex, `<span class="${classe}">$1</span>`);
                }

                return resultado;

            },

            encontrarNumeroAntesDaPalavra(texto, palavraChave) {
                const regex = new RegExp(`(\\d+(?:[.,]\\d+)?)\\s+${palavraChave}`, 'i');
                const match = texto.match(regex);
                return match ? parseFloat(match[1].replace(',', '.')) : null;
            },

            encontrarNumeroDepoisDaPalavra(texto, palavraChave) {
                const textoUpper = texto.toUpperCase();
                const palavra = palavraChave.toUpperCase();

                // Expressão regular:
                // - busca a palavra
                // - permite até 10 caracteres após ela (qualquer caractere)
                // - dentro desses 10 caracteres, procura o primeiro número inteiro
                const regex = new RegExp(`${palavra}[^\\d]{0,10}(\\d+)`);

                const match = textoUpper.match(regex);
                return match ? parseInt(match[1], 10) : null;
            },

            //#region CALCULADORA
            toggleCalc() {
                this.expandida = !this.expandida;
                if (this.valoresCalc.length < 1) {
                    this.adicionarCampo()
                }
                if (!this.expandida) {
                    this.valoresCalc = [];
                }
            },
            adicionarCampo() {

                this.valoresCalc.push(0)
                this.$nextTick(() => {
                    const novoIndex = this.valoresCalc.length - 1
                    const novoInput = document.querySelector(`#input${novoIndex}`);
                    if (novoInput) {
                        novoInput.focus()
                    }
                })

            },

            handleEnter(index) {
                const valor = parseFloat(this.valoresCalc[index])
                if (!isNaN(valor)) {
                    this.adicionarCampo()
                }
            },

            verificaTecla(e, index) {
                if (e.key === '+') {
                    e.preventDefault()
                    this.handleEnter(index)
                }
            },
            inserirValorCalculado() {
                console.log("inserirValorCalculado")
                this.piscarInputs();

                if (this.objProcesso.uniCatUso.length === 1) {
                    this.objProcesso.uniCatUso[0].valor = this.somaTotal;
                    this.$forceUpdate();
                    return;
                }

                this.objProcesso.uniCatUso.forEach(entrada => {
                    if (!entrada.valor) {
                        entrada.valor = this.somaTotal;
                    }
                });
                this.$forceUpdate();
            },

            mostrarToast(mensagem, tipo = 'success') {
                console.log("MOSTRAR TOAST")
                // limpa timeout anterior
                if (this.toast.timeout) {
                    clearTimeout(this.toast.timeout);
                }

                this.toast.mensagem = mensagem;

                const tipos = {
                    success: 'text-bg-success',
                    danger: 'text-bg-danger',
                    warning: 'text-bg-warning',
                    info: 'text-bg-info'
                };

                this.toast.tipoClasse = tipos[tipo] || 'text-bg-secondary';
                this.toast.visivel = true;

                // auto fade-out após 3s
                this.toast.timeout = setTimeout(() => {
                    this.toast.visivel = false;
                    console.log("Ocultar toast")
                }, 3000);
            },

            piscarInputs() {
                let inputs = document.querySelectorAll('.uniCatInput');
                inputs.forEach(input => {
                    input.classList.add('blink')
                    setTimeout(() => {
                        input.classList.remove('blink')
                    }, 400)

                });
            },
            //#endregion

            melhorCorrespondenciaCatUso(texto) {
                try {
                    let txtRetorno = texto.toUpperCase().replace(/[.\s]/g, '');

                    for (const palavra of this.opcoesUniCatUso) {
                        if (txtRetorno.includes(palavra)) {
                            return palavra;
                        }
                    }

                    console.log("txt retorno: ", txtRetorno)

                    return txtRetorno;
                } catch (err) {
                    console.warn("FALHA AO LIMPAR: ", texto, err);
                    return texto;
                }
            },

            mostrarAlerta() {
                alert(this.mensagem)
            },

            procurarOutorga() {
                try {
                    const {
                        docConclusao,
                        docCodReferenciado,
                        docsRelacionados
                    } = this.objProcesso;

                    let contemOutorga = /onerosa/i.test(docConclusao || '');

                    if (!contemOutorga) {
                        contemOutorga = /onerosa/i.test(docCodReferenciado || '');
                    }

                    if (!contemOutorga && Array.isArray(docsRelacionados)) {
                        contemOutorga = docsRelacionados.some(doc => {
                            const texto = doc.doc_txt || '';
                            return /onerosa/i.test(texto);
                        });

                        if (contemOutorga) {
                            this.objProcesso.constaOutorga = contemOutorga;
                            return;
                        }
                    }

                    this.objProcesso.constaOutorga = contemOutorga;
                } catch (err) {
                    console.warn("procurar outorga", err);
                }
            },

            // Validacao de Unidades
            estimarDistribuicaoDeUnidades() {
                if (!this.objProcesso.NumBlocos) {
                    this.objProcesso.NumBlocos = 1;
                }

                let somaDeHis = this.objProcesso.NumUnidadesHIS1 + this.objProcesso.NumUnidadesHIS2;
                if (somaDeHis !== this.objProcesso.NumUnidadesHIS) {
                    this.avisos.push(`Soma HIS (${somaDeHis}) não bate com o valor informado no processo (${this.objProcesso.NumUnidadesHIS})`);
                    if (somaDeHis > 0) {
                        this.objProcesso.NumUnidadesHIS = somaDeHis;
                    }
                }

                let somaDeResidenciais = this.objProcesso.NumUnidadesHIS + this.objProcesso.NumUnidadesHMP + this.objProcesso.NumUnidadesR2hR2v;
                if (somaDeResidenciais !== this.objProcesso.NumUnidadesResidenciais) {
                    this.avisos.push(`Soma de unidades residenciais (${somaDeResidenciais}) não bate com o valor informado no processo (${this.objProcesso.NumUnidadesResidenciais})`);
                    this.objProcesso.NumUnidadesResidenciais = somaDeResidenciais;
                }

                // Divide o numero de unidades pela quantidade de blocos e pavimentos
                let numUnidadesPorBloco = Math.floor(this.objProcesso.NumUnidadesResidenciais / this.objProcesso.NumBlocos)

                this.objProcesso.listaBlocos = [];

                // for (let i = 1; i <= this.objProcesso.NumBlocos; i++) {
                let objBloco = {
                    // indice: i,
                    numPavimentosBloco: this.objProcesso.NumPavimentos,
                    pavimentosNegativos: 0,
                    // maxUnidadesPorPavimento: Math.round(numUnidadesPorBloco / this.objProcesso.NumPavimentos),
                    listaPavimentos: []
                }

                // for (let j = 0; j < this.objProcesso.NumPavimentos; j++) {
                let objPavimento = {
                    listaUnidades: [{
                        cat: ""
                    }]
                }

                objBloco.listaPavimentos.push(objPavimento);
                // }

                this.objProcesso.listaBlocos.push(objBloco);
                // }
            },
            // atribuirProcesso
            async atribuirProcesso() {
                if (!this.podeAtribuirProcessos) {
                    return;
                }


                if (!this.numeroAD || this.numeroAD.length < 5) {
                    alert('O número AD deve possuir pelo menos 5 caracteres.');
                    return;
                }
                if (!this.rfAtribuido || this.rfAtribuido.length < 7) {
                    alert('O login deve ser dXXXXXX');
                    return;
                }


                try {
                    let rfAtual = document.getElementById('rf-usuario')?.textContent?.trim();
                    const response = await fetch('api/atribuirProcesso', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            rfValidador: this.rfAtribuido,
                            numeroAD: this.numeroAD,
                            rfSolicitante: rfAtual
                        })
                    });

                    if (!response.ok) throw new Error('Erro na requisição');

                    const data = await response.json();
                    window.alert('Atribuído com sucesso!');


                } catch (error) {
                    console.error("ERRO AO ATRIBUIR: ", error);
                    this.msgStatus = error.response?.data?.message || 'Erro ao atribuir processo.';
                }
            },

            async validarProcessoUnidades() {
                try {
                    this.downloadObjetoProcesso();

                } catch (e) {
                    console.error("FALHA AO BAIXAR BACKUP DO PROCESSO.", e);
                }
                try {
                    this.isCarregando = true;
                    this.msgStatus = 'Validando informações...';

                    const response = await fetch('api/validarProcessoUnidades', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            objProcesso: this.objProcesso
                        })
                    });

                    if (!response.ok) throw new Error('Erro na requisição');

                    const data = await response.json();
                    // window.alert('Validado com sucesso!');
                    window.location.reload();

                } catch (error) {
                    console.error("ERRO AO VALIDAR: ", error);
                    this.msgStatus = error.response?.data?.message || 'Erro ao validar processo.';
                }
            }

        }

    }).mount('#app')
</script>

</html>
<style>
    #sqlincra {
        position: absolute;
        right: 0;
        margin-top: -3em;
    }

    #constaOutorga {
        width: 30px;
        height: 30px;
        position: absolute;
        right: 6em;
        margin-top: 2em;
        border: 1px solid black;
    }

    #container-principal {
        max-width: 1200px;
    }

    #calculadora {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 200px;
        height: 50px;
        overflow: hidden;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
    }

    #calculadora.expandido {
        height: 200px;
        width: 300px;
    }

    #conteudo-calc {
        padding: 10px;
        display: none;
    }

    #calculadora.expandido #conteudo-calc {
        display: block;
    }

    .HIS1 {
        background-color: #8EA;
    }

    .HIS2 {
        background-color: #8DE;
    }

    .HMP {
        background-color: #CBE;
    }

    .R2v {
        background-color: #EAA;
    }

    .R2h {
        background-color: #F55;
    }

    #contador {
        position: fixed;
        bottom: 10px;
        left: 10px;
        width: 12%;
        min-width: 136px;
        height: 14em;
        font-weight: bold;
    }

    #caixa-multiplicador {
        position: fixed;
        bottom: 16em;
        left: 10px;
        width: 12%;
        min-width: 136px;
        height: 8em;
    }

    #caixa-atribuir {
        position: fixed;
        bottom: 27em;
        left: 10px;
        width: 12%;
        min-width: 136px;
        height: 18em;
    }
</style>