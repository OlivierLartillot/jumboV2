SELECT * FROM `customer_card` 
LEFT JOIN transfer_arrival
ON transfer_arrival.customer_card_id = customer_card.id
LEFT JOIN transfer_inter_hotel
ON transfer_inter_hotel.customer_card_id = customer_card.id
LEFT JOIN transfer_departure
ON transfer_departure.customer_card_id = customer_card.id
LEFT JOIN airport_hotel
ON airport_hotel.id = transfer_arrival.from_start_id OR airport_hotel.id = transfer_arrival.to_arrival_id 
    OR airport_hotel.id = transfer_inter_hotel.from_start_id OR airport_hotel.id = transfer_inter_hotel.to_arrival_id
    OR airport_hotel.id = transfer_departure.from_start_id OR airport_hotel.id = transfer_departure.to_arrival_id
WHERE (transfer_arrival.date >= '2023-05-09' and transfer_arrival.date <= '2023-05-11') 
	OR (transfer_inter_hotel.date >= '2023-05-09' and transfer_inter_hotel.date <= '2023-05-11') 
    OR (transfer_departure.date >= '2023-05-09' and transfer_departure.date <= '2023-05-11') 
AND 
airport_hotel.id = 1


SELECT * FROM `customer_card` 
INNER JOIN transfer_arrival
ON transfer_arrival.customer_card_id = customer_card.id
INNER JOIN transfer_inter_hotel
ON transfer_inter_hotel.customer_card_id = customer_card.id
INNER JOIN airport_hotel
ON airport_hotel.id = transfer_arrival.from_start_id OR airport_hotel.id = transfer_arrival.to_arrival_id 
    OR airport_hotel.id = transfer_inter_hotel.from_start_id OR airport_hotel.id = transfer_inter_hotel.to_arrival_id
WHERE (transfer_arrival.date >= '2023-05-14' and transfer_inter_hotel.date <= '2023-05-20' AND airport_hotel.id = 40) 

// As ton besoin de l airport hotel ?


