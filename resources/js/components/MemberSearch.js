import React, {Component} from 'react'

class MemberSearch extends Component {

    state = {
        initialItems: '',
        items: ''
    };
    
    constructor(props) {
        super(props);
        this.filterList = this.filterList.bind(this);
    } 
    

    filterList(event) {
      let items = this.state.initialItems;
      items = items.filter(
        (item) => {
            return item.toLowerCase().search(event.target.value.toLowerCase()) !== -1;
        }
      );
      this.setState({items: items});
    }

    componentWillMount(){
      this.setState({
          initialItems: this.props.content,
          items: this.props.content
      })
    }

  
    render () {
        return (
            <section className="pageHeaderForm">
                <div className="wrapper">
                    <h2>Команда</h2>
                    <form className="" method="get" action="search.html">
                        <input className="js-widthInput" type="text" value="" onChange={this.filterList} placeholder="Поиск сотрудников" name="s" />
                        <button className="" type="submit">
                            Поиск
                        </button>
                        <div className="hiddenSearch js-widthBlock">
                            <ul>
                                <li><a href="#">vfv</a></li>
                            </ul>
                        </div>
                    </form>

                </div>
            </section>
        )
    }
}

export default MemberSearch