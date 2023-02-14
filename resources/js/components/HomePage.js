import React, {Component} from 'react'
import {getLinks} from '../api/Api'
import {withTranslation} from 'react-i18next'
import data from '../data/data.json';
import {Link} from 'react-router-dom'

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
                        <Link to='/vacation'>{t(data.menu.vacation)}</Link>
                            <div className="home-team">
                                <a href={links[0].url} key={links[0].id}><span>{links[0].title}</span>
                                    <i className="i team"></i>
                                </a>
                            </div>
                            <div className="home-team__inner sc">
                                {links.map((link, index) => {

                                    if (0 !== index) {
                                        const icon = link.icon ? 'i ' + link.icon : 'fa fa-chevron-right';
                                        return (
                                            <div className="home-team__card" key={link.id}>
                                                <a href={link.url}
                                                target="_blank"><span>{link.title}</span>
                                                    <i className={icon}></i>
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
