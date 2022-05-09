class InteractiveSearch {

    static CLASS = "wpcf7-custom-select";
    SELECTED = "wpcf7-custom-select-list-selection";
    LIST_ITEM = "span";

    constructor( id, noResultsText ) {
        this.NO_RESULTS = {
            name: noResultsText
        }
        
        this.helperElement = document.getElementById( `${id}-input-helper` );
        this.inputElement  = document.getElementById( `${id}-input` );
        this.listElement   = document.getElementById( `${id}-list` );
    }

    load() {
        this.db = [];
        const options = this.listElement.children;
        for (let index = 0; index < options.length; index++) {
            const element = options[index];
            this.db.push(
                {
                    name: element.innerHTML,
                    value: element.getAttribute("value")
                }
            );
        }
        this.db = this.db.sort(this.compareOptions);

        this.refreshOptions( this.filterList() );
        this.helperElement.onkeyup = this.searchAsYouType;
    }

    filterList( filter ) {
        const list = this.db;

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

        if ( options.length == 0 ) {
            this.appendOption( this.NO_RESULTS, this.listElement, false );
        } else {
            options.forEach( option => this.appendOption( option, this.listElement ) );
        }
    }

    appendOption( optionObject, element, canClick = true ) {
        let option = this.createOption();
        option.innerHTML = optionObject.name;
        if( canClick ) {
            option.setAttribute("value", optionObject.value);
            option.onclick = this.onSelect;
        }

        element.appendChild( option );
    }

    createOption() {
        const option = document.createElement( this.LIST_ITEM );
        option.classList.add("wpcf7-custom-select-option");
        return option;
    }

    searchAsYouType = event =>  {
        this.inputElement.value = "";
        let filter = event.target.value;
        this.refreshOptions( this.filterList( filter ) );
    }

    onSelect = event => {
        for ( let item of this.listElement.childNodes ) {
            item.classList.remove(this.SELECTED)
        }
        event.target.classList.add( this.SELECTED );
        this.helperElement.value = event.target.innerHTML;
        const selectedValue = event.target.getAttribute("value");
        this.inputElement.value = selectedValue ? selectedValue : event.target.innerHTML;
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

const collection = document.getElementsByClassName(InteractiveSearch.CLASS);
for (let index = 0; index < collection.length; index++) {
    const element = collection[index];
    new InteractiveSearch(element.id, "Nincs ilyen nevű találat!").load();
}