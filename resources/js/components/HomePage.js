import React, {Component} from 'react'

class HomePage extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
        }
    }

    state = {
        search: ""
    };


    render() {

        return (
            <div className="container">
                <section className="home-page">
                    <div className="wrapper">
                        <span>Главная страница мира</span>
                    </div>
                </section>
            </div>
        );
    }
}

export default HomePage

  