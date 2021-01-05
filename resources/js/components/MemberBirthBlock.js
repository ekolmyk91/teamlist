import React, {PureComponent} from 'react'
import moment from 'moment'
import {getBirthPeople} from '../api/Api'
import 'bootstrap/dist/css/bootstrap.css'

class MemberBirthBlock extends PureComponent {

    constructor (props) {
        super(props)
        this.state = {
            monthValue: moment().month(),
            birthPeople: []
        }

        this.handleMonthChange = this.handleMonthChange.bind(this);
    }

    componentDidMount () {
        getBirthPeople(this.state.monthValue).then(data => {
            this.setState({
                birthPeople: data,
            });
        })
    }

    handleMonthChange(event) {
        this.setState({monthValue: event.target.value});
        // console.log(this.state.monthValue);
        getBirthPeople(this.state.monthValue).then(data => {
            this.setState({
                birthPeople: data,
            });
        })
        event.preventDefault();
    }

    renderMonthsList = () => {
        const monthList = moment.months();
        console.log(this.state.monthValue);
        return (
            <div className="filter-inner">
                <select value={this.state.monthValue} onChange={this.handleMonthChange}>
                    {monthList.map((month, index) => (
                        <option key={index} value={index}>{month}</option>
                    ))}
                </select>
                {/*{this.state.monthValue}*/}
            </div>
        );
    }

    renderBirthPeople = () => {
        const renderBirthPeople = this.state.birthPeople.map( (member, id) => (
            <tr key={member.user_id}>
                <td>{member.name}</td>
                <td>{member.surname}</td>
                <td>{(new Date(member.birthday).toLocaleDateString('en-GB', {
                    month: '2-digit',day: '2-digit'}))}</td>
            </tr>
        ));
        return (
            <div>
                <table>
                    <tbody>
                    {renderBirthPeople}
                    </tbody>
                </table>
            </div>
        );
    }

    render() {
        return (
            <div>
                <div>
                    {this.renderMonthsList()}
                </div>
                <div className="card">
                    <div className="card-header">
                        <span className="filter-tittle">Birthday People</span>
                    </div>
                    <div className="card-body">
                        {this.renderBirthPeople()}
                    </div>
                </div>
            </div>
        );
    }
}

export default MemberBirthBlock
