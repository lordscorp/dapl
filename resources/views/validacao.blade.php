<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../resources/css/custom.css?x=1">
</head>

<body>
    <div id="logout">
        <span id="nome-usuario" style="opacity: 0;">Nome: {{ $nome }}</span><span id="rf-usuario">{{ $rf }}</span>
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
        <div class="card mx-auto mt-4" id="container-principal">
            <div class="card-header text-center">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page">Validação</a>
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
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="card-body px-4" v-show="!isCarregando">
                <div id="fixada" style="position: sticky; top: 0; background-color: white; z-index: 10; padding: 10px; border-bottom: 1px solid #ccc;">
                    <div class="card-title">
                        <h3>Processo: @{{ objProcesso.processo }} - @{{ objProcesso.assunto }}</h3>
                    </div>
                    <h4>Categoria identificada: @{{objProcesso.categoria}}</h4>
                    <div id="sqlincra" class="mr-1">SQL: @{{ objProcesso.sqlIncra}}</div>
                    <hr>
                    <div class="row g-1">
                        <div class="col-2" v-if="false">
                            <label class="form-label">Data de Emissão</label>
                            <input type="date" class="form-control" v-model="objProcesso.dtEmissao" disabled>
                        </div>
                        <div class="col-1">
                            <label class="form-label">Blocos</label>
                            <input type="number" class="form-control" v-model="objProcesso.blocos">
                        </div>
                        <div class="col-1">
                            <label class="form-label">Pavimentos</label>
                            <input type="number" class="form-control" v-model="objProcesso.pavimentos">
                        </div>
                        <!-- UNIDADES POR CATEGORIA DE USO -->
                        <div class="col px-2">
                            <label class="form-label">Unidades por categoria de uso</label>
                            <div class="row g-2" v-for="(item, index) in objProcesso.uniCatUso" :key="index">
                                <div class="col">
                                    <select class="form-select" v-model="item.nome">
                                        <option disabled value="">Selecione</option>
                                        <option v-for="opcao in opcoesUniCatUso" :key="opcao" :value="opcao">@{{ opcao }}</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" v-model="item.valor">
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary my-1" @click="adicionarUniCatUso" v-if="podeAdicionarUniCatUso">
                                <strong>+</strong>
                            </button>
                        </div>
                        <!-- AMPARO LEGAL -->
                        <div class="col">
                            <label for="amparoLegal" class="form-label">Amparo Legal</label>
                            <input type="text" class="form-control" id="amparoLegal" v-model="objProcesso.amparoLegal" :title="objProcesso.amparoLegal">
                        </div>
                        <!-- OUTORGA -->
                        <div class="col-2">
                            <div class="form-check">
                                <label class="form-check-label" for="constaOutorga">
                                    Consta Outorga?
                                </label>
                                <input class="form-check-input" type="checkbox" id="constaOutorga" v-model="objProcesso.constaOutorga">
                            </div>
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="container mt-4">
                            <div class="row g-3">
                                <div class="col" v-if="deveExibirAreas">
                                    <label for="areaTotal" class="form-label">Área Total</label>
                                    <input type="number" class="form-control" id="areaTotal" v-model="objProcesso.areaTotal" min="0" step="0.01">
                                </div>

                                <div class="col" v-if="deveExibirAreas">
                                    <label for="areaConstruida" class="form-label">Área Construída</label>
                                    <input type="number" class="form-control" id="areaConstruida" v-model="objProcesso.areaConstruida" min="0" step="0.01">
                                </div>

                                <div class="col" v-if="deveExibirAreas">
                                    <label for="areaComputavel" class="form-label">Área Computável</label>
                                    <input type="number" class="form-control" id="areaComputavel" v-model="objProcesso.areaComputavel" min="0" step="0.01">
                                </div>

                                <div class="col-2" v-if="false">
                                    <label class="form-label">Subprefeitura</label>
                                    <select class="form-select" v-model="objProcesso.subprefeitura">
                                        <option disabled value="">Selecione</option>
                                        <option v-for="opcao in SUBPREFEITURAS" :key="opcao" :value="opcao">@{{ opcao }}</option>
                                    </select>
                                </div>

                                <!-- USO DO IMÓVEL -->
                                <div class="col">
                                    <label for="usoDoImovel" class="form-label">Uso do Imóvel</label>
                                    <input type="text" class="form-control" id="usoDoImovel" v-model="objProcesso.usoDoImovel" :title="objProcesso.usoDoImovel">
                                </div>

                                <div class="col-2">
                                    <label for="zoneamento" class="form-label" title="Zoneamento vigente">Zoneamento</label>
                                    <input type="text" class="form-control" id="zoneamento" v-model="objProcesso.zoneamento">
                                </div>

                                <div class="col-4">
                                    <label for="proprietario" class="form-label">Proprietário</label>
                                    <input type="text" class="form-control" id="proprietario" :title="objProcesso.proprietario" v-model="objProcesso.proprietario">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row my-1">
                        <div class="col">
                            <button class="btn btn-success btn-lg w-100" @click="validarProcesso">Validar</button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 row">
                    <div class="col ml-2" id="txt-conclusao">
                        <div class="card">
                            <div class="card-header">Certificado de Conclusão</div>
                            <div class="card-body txt-doc">
                                <p v-html="destacarTermos(objProcesso.docConclusao)" style="white-space: pre-line;"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col mr-2" id="txt-aprovacao">
                        <div class="card">
                            <div class="card-header">Doc Relacionado</div>
                            <div class="card-body txt-doc">
                                <p v-html="destacarTermos(objProcesso.docCodReferenciado)" style="white-space: pre-line;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div v-if="objProcesso.docsRelacionados && objProcesso.docsRelacionados.length > 0">
                    <h5>Docs Relacionados ao SQL</h5>
                    <div class="mt-4 row" v-for="docRelacionado in objProcesso.docsRelacionados">
                        <div class="col mr-2">
                            <div class="card">
                                <div class="card-header">@{{docRelacionado.assunto}} - @{{docRelacionado.dtEmissao}}</div>
                                <div class="card-body txt-doc">
                                    <p v-html="destacarTermos(docRelacionado.doc_txt)" style="white-space: pre-line;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                SUBPREFEITURAS: ['Aricanduva', 'Butantã', 'Campo Limpo', 'Capela do Socorro', 'Casa Verde', 'Cidade Ademar', 'Cidade Tiradentes', 'Ermelino Matarazzo', 'Freguesia/Brasilândia', 'Guaianases', 'Ipiranga', 'Itaim Paulista', 'Itaquera', 'Jabaquara', 'Jaçanã/Tremembé', 'Lapa', 'MBoi Mirim', 'Mooca', 'Parelheiros', 'Penha', 'Perus/Anhaguera', 'Pinheiros', 'Pirituba/Jaraguá', 'Santana/Tucuruvi', 'Santo Amaro', 'São Mateus', 'São Miguel', 'Sapopemba', 'Sé', 'Vila Maria/Vila Guilherme', 'Vila Mariana', 'Vila Prudente'],

                // opcoesUniCatUsoR: ['EHIS', 'EHMP', 'HIS', 'HMP', 'R1', 'R2'],
                // opcoesUniCatUsoNR: ['nRa', 'nR1', 'nR2', 'nR3', 'Ind 1a', 'Ind 1b', 'Ind 2', 'Ind 3', 'INFRA'],
                opcoesUniCatUsoR: ['HIS', 'HMP', 'R1', 'R2'],
                opcoesUniCatUsoNR: ['nR1', 'nR2'],
                opcoesUniCatUso: [],

                termosDestaque: {
                    'PAVIMENTO': 'destaque-1',
                    'ANDAR': 'destaque-1',
                    'BLOCO': 'destaque-1',
                    'PREDIO': 'destaque-1',
                    'PRÉDIO': 'destaque-1',
                    'UNIDADES': 'destaque-1',
                    'ÁREA': 'destaque-2',
                    'AREA': 'destaque-2',
                    'OUTORGA': 'destaque-2',
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

                objProcesso: {
                    processo: "1234",
                    assunto: "Assunto X",
                    dtEmissao: "2025-10-10",
                    blocos: null,
                    pavimentos: null,
                    docCodReferenciado: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    docConclusao: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    uniCatUso: [{
                        nome: 'HIS',
                        valor: null
                    }],
                    amparoLegal: 'XPTO',
                    usoDoImovel: 'XPTO',
                    constaOutorga: false,
                    areaTotal: 0,
                    areaConstruida: 0,
                    areaComputavel: 0,
                    subprefeitura: '',
                    zoneamento: '',
                    proprietario: '',
                },

            }
        },
        computed: {
            podeAdicionarUniCatUso() {
                const ultimo = this.objProcesso.uniCatUso[this.objProcesso.uniCatUso.length - 1];
                if (!ultimo) {
                    return true;
                }
                return ultimo.nome && ultimo.valor !== null;
            }
        },
        mounted() {
            this.carregarOptions();
            this.carregarProcesso();
        },
        methods: {
            adicionarUniCatUso() {
                this.objProcesso.uniCatUso.push({
                    nome: '',
                    valor: null
                });
            },

            carregarOptions() {
                this.opcoesUniCatUso = this.opcoesUniCatUsoR.concat(this.opcoesUniCatUsoNR);
            },

            async carregarProcesso() {
                const rfValidador = document.getElementById('rf-usuario')?.textContent?.trim();

                if (!rfValidador) {
                    console.warn('rfValidador não encontrado');
                    return;
                }

                try {
                    this.isCarregando = true;
                    const response = await fetch(`api/processoAValidar?rfValidador=${encodeURIComponent(rfValidador)}`);
                    if (!response.ok) throw new Error('Erro ao buscar processo');

                    const data = await response.json();
                    this.objProcesso = data.objProcesso;

                    // this.objProcesso.categoria = this.extrairLinha21(data.objProcesso.docAprovacao);

                    if (!this.objProcesso.uniCatUso) {
                        this.objProcesso.uniCatUso.push({
                            nome: 'HIS',
                            valor: null
                        })
                    }
                    this.procurarCatUso();
                    this.objProcesso.blocos = this.procurarBlocos();
                    this.objProcesso.pavimentos = this.procurarPavimentos();
                    this.atribuirAreas();
                    // this.procurarProprietario();

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

            procurarCatUso() {
                try {
                    let catUsoPrincipal = this.melhorCorrespondenciaCatUso(this.objProcesso.categoria);
                    this.objProcesso.uniCatUso[0].nome = catUsoPrincipal;
                    this.objProcesso.uniCatUso[0].valor = this.procurarUnidades();
                } catch (err) {
                    console.warn("Não achou catUso", err);
                }
            },

            procurarUnidades() {
                try {
                    let numUnidades = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'UNIDADE');
                    if (!numUnidades) {
                        numUnidades = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docCodReferenciado, 'UNIDADE');
                    }
                    if (!numUnidades) {
                        numUnidades = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docCodReferenciado, 'UNIDADE');
                    }
                    return numUnidades;
                } catch (err) {
                    console.warn("procurar unidades: ", err);
                }
                return null;
            },
            procurarBlocos() {
                try {
                    let numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'PREDIO');
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'BLOCO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docCodReferenciado, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docCodReferenciado, 'BLOCO');
                    }
                    // DEPOIS DA PALAVRA
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'BLOCO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docCodReferenciado, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docCodReferenciado, 'BLOCO');
                    }
                    // FIM DEPOIS DA PALAVRA
                    if (!numBlocos) {
                        numBlocos = 1;
                    }
                    return numBlocos;
                } catch (err) {
                    console.warn("procurar Blocos: ", err);
                }
                return null;
            },
            procurarPavimentos() {
                try {
                    let numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'ANDAR');
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'PAVIMENTO');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docCodReferenciado, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docCodReferenciado, 'PAVIMENTO');
                    }
                    // DEPOIS PALAVRA
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'PAVIMENTO');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docCodReferenciado, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docCodReferenciado, 'PAVIMENTO');
                    }
                    // FIM DEPOIS PALAVRA
                    if (!numPavimentos) {
                        return 1;
                    }
                    return numPavimentos;
                } catch (err) {
                    console.warn("procurar Pavimentos: ", err);
                }
                return null;
            },
            procurarProprietario() {
                let nomeProprietario = null;

                try {
                    const match = this.objProcesso.docConclusao.match(/PROPRIET[ÁA]RIO\s*:\s*(.+)/i);
                    if (match) {
                        nomeProprietario = match[1].split('\n')[0].trim();
                    }
                } catch (err) {
                    console.warn("Erro ao procurar proprietario no doc conclusao", err);
                }

                if (!nomeProprietario) {
                    this.objProcesso.docsRelacionados.forEach(doc => {
                        if (nomeProprietario) return;

                        const texto = doc.doc_txt || '';
                        const matchR = texto.match(/PROPRIET[ÁA]RIO\s*:\s*(.+)/i);
                        if (matchR) {
                            nomeProprietario = matchR[1].split('\n')[0].trim();
                        }
                    });
                }

                this.objProcesso.proprietario = nomeProprietario;

                this.$forceUpdate();
            },
            procurarAreas() {
                // this.areaComputavel = 
            },

            traduzir(campo) {
                switch (campo) {
                    case "doc_txt":
                        return "Texto do Documento"
                        break;

                    default:
                        return campo;
                        break;
                }
            },

            async validarProcesso() {
                try {
                    this.isCarregando = true;
                    this.msgStatus = 'Validando informações...';

                    const response = await fetch('api/validarProcesso', {
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
                    window.alert('Validado com sucesso!');
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
        right: 4em;
        margin-top: 2em;
        border: 1px solid black;
    }

    #container-principal {
        max-width: 1200px;
    }
</style>