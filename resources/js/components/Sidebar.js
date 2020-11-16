import React, {Component} from 'react'
import {getDepartments} from '../api/Api'

class Sidebar extends Component {

    constructor (props) {
        super(props)
    }
    
    componentDidMount () {
        getDepartments().then(data => {
            console.log(data)
        })
    }


    render() {
        return (
            <div className="sidebar-inner">
                asd
            </div>
        );
    }
}

export default Sidebar