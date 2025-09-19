import React, { useState, useEffect } from 'react';
export default function WeatherApp() {
    console.log('WeatherApp component rendered');
    const [cities, setCities] = useState([]);
    const [newCity, setNewCity] = useState('');
    const [loading, setLoading] = useState(false);

    const loadUserCities = async () => {
        try {
            const response = await fetch('/api/user/cities');
            if (response.ok) {
                const data = await response.json();
                setCities(data);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    const addCity = async () => {
        if (!newCity.trim()) return;
        
        setLoading(true);
        try {
            const response = await fetch('/api/user/cities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cityName: newCity.trim() })
            });
            
            if (response.ok) {
                const data = await response.json();
                setCities(prev => [...prev, data]);
                setNewCity('');
            }
        } catch (error) {
            console.error('Error adding:', error);
        } finally {
            setLoading(false);
        }
    };

    const removeCity = async (cityId) => {
        try {
            const response = await fetch(`/api/user/cities/${cityId}`, {
                method: 'DELETE'
            });
            
            if (response.ok) {
                setCities(prev => prev.filter(city => city.id !== cityId));
            }
        } catch (error) {
            console.error('Error deleting:', error);
        }
    };

    useEffect(() => {
        loadUserCities();
    }, []);


    return (
        <div className="weather-app">
            <h2>Мои города</h2>
            
            <div className="add-city-form">
                <input
                    type="text"
                    value={newCity}
                    onChange={(e) => setNewCity(e.target.value)}
                    placeholder="Введите название города на английском"
                    onKeyPress={(e) => e.key === 'Enter' && addCity()}
                />
                <button onClick={addCity} disabled={loading}>
                    {loading ? 'Добавление...' : 'Добавить'}
                </button>
            </div>

            <div className="cities-list">
                {cities.map(city => (
                    <div key={city.id} className="city-card">
                        <span>{city.cityName}</span>
                        <button 
                            onClick={() => removeCity(city.id)}
                            className="remove-btn"
                        >
                            Удалить
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
}