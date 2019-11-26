import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import { BrowserRouter, Route, Switch } from 'react-router-dom'
import Header from './Header'
import MemberSearch from './MemberSearch'
import TeamList from './TeamList'

class App extends Component {
    render () {
        return (
            <BrowserRouter>
                <Header />
                <div className="content">
                    <MemberSearch />
                    <Switch>
                        <Route path='/team' component={TeamList} />
                    </Switch>
                </div>
            </BrowserRouter>
        )
    }
}

ReactDOM.render(<App />, document.getElementById('app'))
