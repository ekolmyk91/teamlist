import React, {Component} from 'react'
import {getUsers} from '../api/Api'

class MemberSearch extends Component {

    constructor(props) {
        super(props);
        this.filterList = this.filterList.bind(this);  
        this.state = {
            members: []
        }
    } 

    state = {
        members: []
    };    
    
    componentDidMount () {
        getUsers().then(response => {
                this.setState({
                    members: response.data
                });
            })
    }

    filterList(event) {
        let members = this.state.initialItems;
        members = members.filter(
            (member) => {
                return member.toLowerCase().search(event.target.value.toLowerCase()) !== -1;
            }
        );
        this.setState({members: members});
    }

    componentWillMount(){
      this.setState({
          // members: this.props.content
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