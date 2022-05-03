class InteractiveSearch {

    static CLASS = "wpcf7-custom-select";
    SELECTED = "wpcf7-custom-select-list-selection";
    LIST_ITEM = "li";

    constructor( id, noResultsText ) {
        this.NO_RESULTS = noResultsText;
        console.log(`creating ${id}`);
        this.inputElement = document.getElementById( `${id}-input` );
        this.listElement = document.getElementById( `${id}-list` );
    }

    load() {
        this.db = [];
        for (const option in this.listElement.children) {
            this.db.push(
                {
                    name: option.innerHTML,
                    value: option.value
                }
            )
        }

        this.refreshOptions( this.filterList() );
        this.inputElement.onkeyup = this.searchAsYouType;
    }

    filterList( filter ) {
        const list = this.db.sort(this.compareOptions);
        if( !filter ) {
            return list;
        } else {
            return list.filter( option => option.name.toLowerCase().includes( filter.toLowerCase() ) );
        }
    }

    refreshOptions( options ) {
        // Delete all options.
        while(this.listElement.hasChildNodes()) {
            this.listElement.removeChild( this.listElement.firstChild );
        }

        options.forEach( option => this.appendOption( option, this.listElement ) );
        if ( options.length == 0 ) {
            this.appendOption( this.NO_RESULTS, this.listElement, false );
        }
    }

    appendOption( optionObject, element, canClick = true ) {
        let option = document.createElement( this.LIST_ITEM );
        option.innerHTML = optionObject.name;
        option.value = optionObject.value;
        if( canClick ) {
            option.onclick = this.onSelect;
        }

        element.appendChild( option );
    }

    searchAsYouType = event =>  {
        let filter = event.target.value;
        this.refreshOptions( this.filterList( filter ) );
    }

    onSelect = event => {
        for ( let item of this.listElement.childNodes ) {
            item.classList.remove(this.SELECTED)
        }
        event.target.classList.add( this.SELECTED );
        this.inputElement.value = event.target.innerHTML;
    }

    compareOptions( o1, o2 ) {
        if ( o1.name < o2.name ) {
            return -1;
        }
        if ( o1.name > o2.name ) {
            return 1;
        }
        return 0;
    }
}

for (const element in document.getElementsByClassName(InteractiveSearch.CLASS)) {
    new InteractiveSearch(element.id, 'Nincs ilyen nevű találat!').load();
}