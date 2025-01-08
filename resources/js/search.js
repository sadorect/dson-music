window.searchHandler = function() {
    return {
        query: '',
        mobileQuery: '',
        results: [],
        
        async performSearch() {
            const searchQuery = this.query || this.mobileQuery;
            if (searchQuery.length < 2) {
                this.results = [];
                return;
            }
            
            try {
                const response = await fetch(`/search/quick?q=${searchQuery}`);
                const data = await response.json();
                this.results = data;
            } catch (error) {
                console.error('Search failed:', error);
            }
        }
    }
}
