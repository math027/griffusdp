/**
 * TablePaginator - Classe para paginação de tabelas
 * 
 * @param {Object} options - Opções de configuração
 * @param {string} options.tableId - ID da tabela a paginar
 * @param {string} options.containerId - ID do container onde os controles serão renderizados
 * @param {number} [options.itemsPerPage=10] - Número de itens por página
 * @param {boolean} [options.showPageSize=true] - Mostrar seletor de tamanho de página
 * @param {Array<number>} [options.pageSizeOptions=[10,25,50,100]] - Opções de tamanho de página
 */
class TablePaginator {
    constructor(options) {
        this.tableId = options.tableId;
        this.containerId = options.containerId;
        this.itemsPerPage = options.itemsPerPage || 10;
        this.showPageSize = options.showPageSize !== false;
        this.pageSizeOptions = options.pageSizeOptions || [10, 25, 50, 100];
        this.currentPage = 1;
        
        this.table = document.getElementById(this.tableId);
        this.container = document.getElementById(this.containerId);
        
        if (!this.table) {
            console.error(`Table with id "${this.tableId}" not found`);
            return;
        }
        
        if (!this.container) {
            console.error(`Container with id "${this.containerId}" not found`);
            return;
        }
    }
    
    /**
     * Retorna as linhas visíveis da tabela (tbody tr que não estão com display: none)
     */
    getVisibleRows() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return [];
        
        const rows = Array.from(tbody.querySelectorAll('tr'));
        return rows.filter(row => {
            // Retorna linhas não escondidas por filtros
            // OU que foram escondidas apenas pela paginação
            return row.style.display !== 'none' || row.dataset._pgHidden === '1';
        });
    }
    
    /**
     * Aplica a paginação às linhas visíveis
     */
    apply() {
        const visibleRows = this.getVisibleRows();
        const totalItems = visibleRows.length;
        const totalPages = Math.ceil(totalItems / this.itemsPerPage);
        
        // Ajusta a página atual se necessário
        if (this.currentPage > totalPages && totalPages > 0) {
            this.currentPage = totalPages;
        }
        if (this.currentPage < 1) {
            this.currentPage = 1;
        }
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        
        visibleRows.forEach((row, i) => {
            if (i < startIndex || i >= endIndex) {
                // Esconde e marca como escondido pela paginação
                row.style.display = 'none';
                row.dataset._pgHidden = '1';
            } else {
                // Remove a marcação de paginação e mostra
                delete row.dataset._pgHidden;
                row.style.display = '';
            }
        });
        
        // Renderiza os controles
        this.renderControls(totalItems, totalPages);
    }
    
    /**
     * Renderiza os controles de paginação
     */
    renderControls(totalItems, totalPages) {
        if (!this.container) return;
        
        if (totalItems === 0) {
            this.container.innerHTML = '';
            return;
        }
        
        const startItem = totalItems > 0 ? ((this.currentPage - 1) * this.itemsPerPage) + 1 : 0;
        const endItem = Math.min(this.currentPage * this.itemsPerPage, totalItems);
        
        let html = '<div class="pagination-container">';
        
        // Informação de items
        html += `<div class="pagination-info">`;
        html += `Mostrando ${startItem} a ${endItem} de ${totalItems} ${totalItems === 1 ? 'item' : 'itens'}`;
        html += `</div>`;
        
        // Controles de página
        html += '<div class="pagination-controls">';
        
        // Botão Primeira Página
        html += `<button class="pagination-btn pagination-nav-btn" 
                        onclick="window.${this.getInstanceName()}.goToPage(1)" 
                        ${this.currentPage === 1 || totalPages === 0 ? 'disabled' : ''}>
                    <i class="fas fa-angles-left"></i>
                </button>`;
        
        // Botão Página Anterior
        html += `<button class="pagination-btn pagination-nav-btn" 
                        onclick="window.${this.getInstanceName()}.goToPage(${this.currentPage - 1})" 
                        ${this.currentPage === 1 || totalPages === 0 ? 'disabled' : ''}>
                    <i class="fas fa-angle-left"></i>
                </button>`;
        
        // Botões de números de página
        const pageNumbers = this.getPageNumbers(totalPages);
        pageNumbers.forEach(pageNum => {
            if (pageNum === '...') {
                html += `<span class="pagination-btn pagination-page-btn" style="border:none;cursor:default;">...</span>`;
            } else {
                html += `<button class="pagination-btn pagination-page-btn ${pageNum === this.currentPage ? 'active' : ''}" 
                                onclick="window.${this.getInstanceName()}.goToPage(${pageNum})">
                            ${pageNum}
                        </button>`;
            }
        });
        
        // Botão Próxima Página
        html += `<button class="pagination-btn pagination-nav-btn" 
                        onclick="window.${this.getInstanceName()}.goToPage(${this.currentPage + 1})" 
                        ${this.currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>
                    <i class="fas fa-angle-right"></i>
                </button>`;
        
        // Botão Última Página
        html += `<button class="pagination-btn pagination-nav-btn" 
                        onclick="window.${this.getInstanceName()}.goToPage(${totalPages})" 
                        ${this.currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>
                    <i class="fas fa-angles-right"></i>
                </button>`;
        
        html += '</div>'; // fecha pagination-controls
        
        // Seletor de tamanho de página
        if (this.showPageSize) {
            html += '<div class="pagination-page-size">';
            html += '<label>Itens por página:</label>';
            html += `<select onchange="window.${this.getInstanceName()}.changePageSize(this.value)">`;
            this.pageSizeOptions.forEach(size => {
                html += `<option value="${size}" ${size === this.itemsPerPage ? 'selected' : ''}>${size}</option>`;
            });
            html += '</select>';
            html += '</div>';
        }
        
        html += '</div>'; // fecha pagination-container
        
        this.container.innerHTML = html;
    }
    
    /**
     * Retorna os números de página a serem exibidos
     */
    getPageNumbers(totalPages) {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, i) => i + 1);
        }
        
        const pages = [];
        const current = this.currentPage;
        
        // Sempre mostra primeira página
        pages.push(1);
        
        if (current <= 3) {
            // Início: 1 2 3 4 ... last
            pages.push(2, 3, 4, '...', totalPages);
        } else if (current >= totalPages - 2) {
            // Fim: 1 ... n-3 n-2 n-1 n
            pages.push('...', totalPages - 3, totalPages - 2, totalPages - 1, totalPages);
        } else {
            // Meio: 1 ... current-1 current current+1 ... last
            pages.push('...', current - 1, current, current + 1, '...', totalPages);
        }
        
        return pages;
    }
    
    /**
     * Vai para uma página específica
     */
    goToPage(page) {
        const visibleRows = this.getVisibleRows();
        const totalPages = Math.ceil(visibleRows.length / this.itemsPerPage);
        
        if (page < 1 || page > totalPages) return;
        
        this.currentPage = page;
        this.apply();
    }
    
    /**
     * Muda o número de itens por página
     */
    changePageSize(newSize) {
        this.itemsPerPage = parseInt(newSize);
        this.currentPage = 1;
        this.apply();
    }
    
    /**
     * Reseta a paginação para a primeira página
     */
    reset() {
        // Mostra todas as linhas que foram escondidas pela paginação
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.querySelectorAll('tr[data-_pg-hidden="1"]').forEach(row => {
                row.style.display = '';
                delete row.dataset._pgHidden;
            });
        }
        this.currentPage = 1;
    }
    
    /**
     * Retorna o nome da instância global (para callbacks onclick)
     */
    getInstanceName() {
        // Tenta encontrar o nome da variável global que contém esta instância
        // Primeiro, verifica as variáveis comuns usadas no código
        const commonNames = ['pagerCv', 'pagerFunc', 'pagerVagas', 'pagerContratos', 'pagerSelecao', 'pagerAniversariantes'];
        
        for (const name of commonNames) {
            if (window[name] === this) {
                return name;
            }
        }
        
        // Se não encontrou, cria uma variável global baseada no tableId
        const instanceName = `pager_${this.tableId}`;
        window[instanceName] = this;
        return instanceName;
    }
}

// Disponibiliza globalmente
if (typeof window !== 'undefined') {
    window.TablePaginator = TablePaginator;
}
