class InteractiveSearch {

    SELECTED = "wpcf7-custom-select-list-selection";
    LIST_ITEM = "li";

    constructor( id, noResultsText ) {
        this.NO_RESULTS = noResultsText;
        
        this.inputElement = document.getElementById( `${id}-input` );
        this.listElement = document.getElementById( `${id}-list` );
    }

    async load( url ) {
        this.db = await fetch(url).then( response => response.json() ).then( data => {
            return data.values;
        }).catch( error => {
            console.warn(error);
        });
        this.refreshOptions( this.filterList() );
        this.inputElement.onkeyup = this.searchAsYouType;
    }

    filterList( filter ) {
        const list = this.db.map( entry => `${entry.name} - ${entry.other}` ).sort();
        if( !filter ) {
            return list;
        } else {
            return list.filter( value => value.toLowerCase().includes( filter.toLowerCase() ) );
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

    appendOption( name, element, canClick = true ) {
        let option = document.createElement( this.LIST_ITEM );
        option.innerHTML = name;
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
}

new InteractiveSearch("high-school", "Nincs ilyen nevű találat!").load("/wp-content/plugins/mm-wpcf7-extension/modules/custom-blocks/scripts/schools.json");
//new InteractiveSearch("university", "Nincs ilyen nevű találat!").load("/wp-content/plugins/mm-wpcf7-extension/modules/custom-blocks/scripts/universities.json");