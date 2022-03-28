import React, {Component} from 'react'
import {getLinks} from '../api/Api'
import {withTranslation} from 'react-i18next'
import data from '../data/data.json';

class HomePage extends Component {
    constructor(props) {
        super(props)
        this.state = {
            links: [],
            isLoaded: false,
        }
    }

    componentDidMount() {
        getLinks().then(data => {
            console.log(data)
            this.setState({
                links: data,
                isLoaded: true
            });
        })
    }


    render() {
        const {t} = this.props;
        const {isLoaded, links} = this.state;
        if (!isLoaded) {
            return <div className="preloader">Loading...</div>;
        } else {
            return (
                <div>
                    <section className="pageHeaderForm">
                        <div className="wrapper">
                            <h2>{t(data.home)}</h2>
                        </div>
                    </section>
                    <div className="container">
                        <section className="home-page">
                            <div className="home-team">
                                <a href="/team"><span>{t(data.homepage_content.team)}</span>
                                    <i className="i team"></i>
                                </a>
                            </div>
                            <div className="home-team__inner sc">
                                {links.map((link, index) => {

                                    if (0 !== index) {
                                        return (
                                            <div className="home-team__card">
                                                <a href="https://forms.gle/PoY7USJyU2CU5ZLKA"
                                                target="_blank"><span>{t(data.homepage_content.request)}</span>
                                                    <i className="i pc"></i>
                                                </a>
                                            </div>
                                        )
                                    }
                                })}
                            </div>
                        </section>
                    </div>
                </div>
            );
        }
    }
}

export default withTranslation()(HomePage)
