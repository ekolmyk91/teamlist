import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import { BrowserRouter, Route, Switch } from 'react-router-dom'
import Header from './Header'
import TeamList from './TeamList'
import HomePage from './HomePage'

class App extends Component {
    render () {
        return (
            <BrowserRouter>
                <Header />
                <div className="content">
                    <Switch>
                        <Route path='/team' component={TeamList} />
	                    <Route path='/' component={HomePage} />
                    </Switch>
                </div>
            </BrowserRouter>
        )
    }
}

ReactDOM.render(<App />, document.getElementById('app'))
